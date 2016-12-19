<?php
// Routes

$app->get('/[{amount}]', App\Action\Top3::class);
