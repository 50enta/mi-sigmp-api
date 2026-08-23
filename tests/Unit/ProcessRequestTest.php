<?php

use App\Http\Requests\ProcessRequest;
use App\Models\User;

it('uses the authenticated person as the process registrar', function () {
    $user = new User;
    $user->pessoa_id = 42;

    $request = new class extends ProcessRequest
    {
        public function authorize(): bool
        {
            return true;
        }

        public function rules(): array
        {
            return [];
        }

        public function prepare(): void
        {
            $this->prepareForValidation();
        }
    };

    $request->replace(['abertoPor' => 999]);
    $request->setUserResolver(fn () => $user);
    $request->prepare();

    expect($request->input('abertoPor'))->toBe(42);
});
