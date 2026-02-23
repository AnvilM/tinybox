<?php

use Nyholm\Psr7\ServerRequest;
use Symfony\Component\Console\Exception\CommandNotFoundException;

it('example', function () {
    expect($this->app->handle(new ServerRequest('GET', '/'))->getStatusCode())->toBe(200);
});

it('example cli', function () {
    expect(fn() => $this->cliApp->find('example'))
        ->toThrow(CommandNotFoundException::class);
});

