<?php

use Shamarkellman\PowerTranz\Responses\AbstractResponse;

class TestResponse extends AbstractResponse {
    public function __construct(array $data) {
        $this->transactionData = (object) $data;
    }
}

test('AbstractResponse subclass stores and returns data', function () {
    $data = ['foo' => 'bar'];
    $response = new TestResponse($data);
    expect($response->getData())->toBeInstanceOf(stdClass::class);
    expect($response->getData()->foo)->toBe('bar');
});
