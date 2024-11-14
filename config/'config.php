// config.php
return [
    'paypal' => [
        'client_id' => 'AX4wOIQq5BNLsS4eBtLgpVyqpG11-CK772Lxz7sYCFO9hVISUZXuZxhx-yVQwSP-xuiHvNm-hTGZJqqG',
        'secret' => 'EAoDDbUdbYX-QTB2PZ81q8pPG51t5CZYl8_N_mXYsAKxLLIv29cUkefkCs4ShpjsYW8etKyyEaew2e4T',
        'settings' => [
            'mode' => 'sandbox', // o 'live' si estás en producción
            'http.ConnectionTimeOut' => 30,
            'log.LogEnabled' => true,
            'log.FileName' => '../PayPal.log',
            'log.LogLevel' => 'FINE'
        ],
    ],
];
