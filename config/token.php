<?php

return [
    // 加密KEY
    'key'    => 'JKHJHJHJJK&IUYBY&*^!Q*)NSYUG*&!H(UWINB!(*DUIHAHB&*',
    // 签发者
    'iss'    => 'HSK',
    // 默认接收者
    'aud'    => 'Client',
    // 默认生效时间，某个时间点后才能访问
    'nbf'    => 0,
    // 默认过期时间
    'exp'    => 7200,
    // 过期余留时长
    'leeway' => 0,
];