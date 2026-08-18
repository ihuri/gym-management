<?php

test('a rota principal redireciona para o painel administrativo /admin', function () {
    $response = $this->get('/');

    $response->assertRedirect('/admin');
});
