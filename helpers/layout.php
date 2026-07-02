<?php
// helpers/layout.php

function renderHeader($title = "KMI App")
{
    require __DIR__ . '/../app/Views/layouts/header.php';
}

function renderFooter()
{
    require __DIR__ . '/../app/Views/layouts/footer.php';
}
?>