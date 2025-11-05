<?php
return [
    'required'  => ':attribute を入力してください',
    'email'     => ':attribute はメール形式で入力してください',
    'confirmed' => ':attribute と一致しません',
    'unique'    => 'その :attribute は既に使用されています',
    'min' => [
        'string' => ':attribute は:min文字以上で入力してください',
    ],
    'max' => [
        'string' => ':attribute は:max文字以内で入力してください',
    ],

    // 項目名（ここが表示名になる）
    'attributes' => [
        'name'                  => 'ユーザー名',
        'email'                 => 'メールアドレス',
        'password'              => 'パスワード',
        'password_confirmation' => 'パスワード（確認）',
    ],
];
