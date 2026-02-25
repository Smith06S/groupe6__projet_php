<?php
require_once __DIR__ . '/../db/Database.php';

function VerifyIdentifiant($username, $password) {
    $mysqli = dbConnect();

    if ($username === '' || $password === '') {
        return "Nom d'utilisateur et mot de passe requis.";
    }

    $stmt = $mysqli->prepare("SELECT id, mdp FROM user WHERE username = ? LIMIT 1");

    if ($stmt === false) {
        return "Erreur SQL (prepare) : " . $mysqli->error;
    }

    $stmt->bind_param("s", $username);

    if ($stmt->execute()) {
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($userId, $hashedPassword);
            $stmt->fetch();

            if (password_verify($password, $hashedPassword)) {
                $_SESSION['user_id'] = $userId;
                $_SESSION['username'] = $username;
                header("Location: /php_exam/groupe6__projet_php/Vue/products/");
                exit;
            } else {
                $message = "Nom d'utilisateur ou mot de passe incorrect.";
            }
        } else {
            $message = "Nom d'utilisateur ou mot de passe incorrect.";
        }
    } else {
        $message = "Erreur : " . $stmt->error;
    }

    $stmt->close();

    return $message ?? null;
}



function registerIdentifiant($username, $mail, $password) {
    $mysqli = dbConnect();

    if ($username === '' || $mail === '' || $password === '') {
        return "Merci de remplir tous les champs.";
    } else {
        $stmtExists = $mysqli->prepare("SELECT id FROM user WHERE username = ? OR mail = ? LIMIT 1");

        if ($stmtExists === false) {
            return "Erreur SQL (prepare check) : " . $mysqli->error;
        } else {
            $stmtExists->bind_param("ss", $username, $mail);
            if (!$stmtExists->execute()) {
                $error = "Erreur vérification utilisateur : " . $stmtExists->error;
                $stmtExists->close();
                return $error;
            }
            $stmtExists->store_result();

            if ($stmtExists->num_rows > 0) {
                $message = "Username ou email déjà utilisé.";
            } else {
                $stmt = $mysqli->prepare("INSERT INTO user (username, mail, mdp, role) VALUES (?, ?, ?, 'user')");

                if ($stmt === false) {
                    $message = "Erreur SQL (prepare insert) : " . $mysqli->error;
                } else {
                    $stmt->bind_param("sss", $username, $mail, $password);

                    if ($stmt->execute()) {
                        $_SESSION['user_id'] = $stmt->insert_id;
                        $_SESSION['username'] = $username;
                        header("Location: /php_exam/groupe6__projet_php/Vue/products/");
                        exit;
                    } else {
                        if ($stmt->errno === 1062) {
                            $message = "Username ou email déjà utilisé.";
                        } else {
                            $message = "Erreur : " . $stmt->error;
                        }
                    }

                    $stmt->close();
                }
            }

            $stmtExists->close();
            return $message;
        }
    }
}


function checkUnique($username, $mail, $excludeUserId = 0) {
    $mysqli = dbConnect();
    $stmt = $mysqli->prepare("SELECT id FROM user WHERE (username = ? OR mail = ?) AND id <> ? LIMIT 1");

    if ($stmt === false) {
        $mysqli->close();
        return false;
    }

    $stmt->bind_param("ssi", $username, $mail, $excludeUserId);
    $stmt->execute();
    $stmt->store_result();
    $exists = ($stmt->num_rows > 0);

    $stmt->close();
    $mysqli->close();

    return $exists;
}

function userUpdate($username, $mail, $photo_profil, $userId, $hashedPassword = null) {
    $mysqli = dbConnect();

    if ($hashedPassword !== null) {
        $stmt = $mysqli->prepare("UPDATE user SET username = ?, mail = ?, photo_profil = ?, mdp = ? WHERE id = ?");
        if ($stmt === false) {
            $mysqli->close();
            return false;
        }
        $stmt->bind_param("ssssi", $username, $mail, $photo_profil, $hashedPassword, $userId);
    } else {
        $stmt = $mysqli->prepare("UPDATE user SET username = ?, mail = ?, photo_profil = ? WHERE id = ?");
        if ($stmt === false) {
            $mysqli->close();
            return false;
        }
        $stmt->bind_param("sssi", $username, $mail, $photo_profil, $userId);
    }

    $ok = $stmt->execute();
    $stmt->close();
    $mysqli->close();

    return $ok;
}

function addMoney($amount, $userId) {
    $mysqli = dbConnect();
    $stmt = $mysqli->prepare("UPDATE user SET solde = solde + ? WHERE id = ?");

    if ($stmt === false) {
        $mysqli->close();
        return false;
    }

    $stmt->bind_param("di", $amount, $userId);
    $ok = $stmt->execute();
    $stmt->close();
    $mysqli->close();

    return $ok;
}

function getUserById($userId) {
    $mysqli = dbConnect();
    $stmt = $mysqli->prepare("SELECT id, username, mail, solde, photo_profil, role FROM user WHERE id = ? LIMIT 1");

    if ($stmt === false) {
        $mysqli->close();
        return null;
    }

    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result ? $result->fetch_assoc() : null;

    $stmt->close();
    $mysqli->close();

    return $user;
}

function userArticles($userId) {
    $mysqli = dbConnect();
    $stmt = $mysqli->prepare("SELECT id, nom, description, prix, date_publication, image_url FROM article WHERE auteur_id = ? ORDER BY date_publication DESC");

    if ($stmt === false) {
        $mysqli->close();
        return [];
    }

    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $articles = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

    $stmt->close();
    $mysqli->close();

    return $articles;
}

function userArticlePurchased($userId) {
    $mysqli = dbConnect();
    $stmt = $mysqli->prepare("SELECT a.id, a.nom, a.description, ii.quantity, ii.price, i.transaction_date
                              FROM invoice_item ii
                              INNER JOIN invoice i ON i.id = ii.invoice_id
                              INNER JOIN article a ON a.id = ii.article_id
                              WHERE i.user_id = ?
                              ORDER BY i.transaction_date DESC, a.id DESC");

    if ($stmt === false) {
        $mysqli->close();
        return [];
    }

    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $articles = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

    $stmt->close();
    $mysqli->close();

    return $articles;
}

function userArticleBought($userId) {
    $mysqli = dbConnect();
    $stmt = $mysqli->prepare("SELECT id, transaction_date, montant, facturation_address, facturation_city, facturation_zip
                              FROM invoice
                              WHERE user_id = ?
                              ORDER BY transaction_date DESC");

    if ($stmt === false) {
        $mysqli->close();
        return [];
    }

    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $invoices = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

    $stmt->close();
    $mysqli->close();

    return $invoices;
}