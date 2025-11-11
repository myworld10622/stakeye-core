<?php

return [
    // keep purchase code in config so config:cache works reliably
    'purchase_code' => env('PURCHASECODE', null),
];
