<?php
function product_index() : void
{
    require_once __DIR__ . '/../Vue/products/Home.php';
}

function product_show($id) : void
{
    require_once __DIR__ . '/../db/Database.php';

    $message = '';
    $stockQuantityColumn = null;
    $id = intval($id ?? 0);
    $result = false;

    $stmt = new class {
        public function close(): void
        {
        }
    };

    if ($id > 0) {
        $prepared = $mysqli->prepare('SELECT * FROM article WHERE id = ? LIMIT 1');
        if ($prepared) {
            $prepared->bind_param('i', $id);
            $prepared->execute();
            $result = $prepared->get_result();
            $stmt = $prepared;
        } else {
            $message = 'Impossible de charger cet article.';
        }
    } else {
        $message = 'Identifiant article invalide.';
    }

    require_once __DIR__ . '/../Vue/products/Detail.php';
}

function product_create() : void
{
    $message = '';
    $sessionUserId = intval($_SESSION['user_id'] ?? 0);
    require_once __DIR__ . '/../Vue/products/Sell.php';
}

function product_edit($id) : void
{
    $message = '';
    $id = intval($id ?? 0);
    $article = [
        'nom' => '',
        'description' => '',
        'prix' => '0.00',
        'image_url' => '',
        'date_publication' => date('Y-m-d'),
    ];
    $dateInputValue = $article['date_publication'];

    require_once __DIR__ . '/../Vue/products/Edit.php';
}