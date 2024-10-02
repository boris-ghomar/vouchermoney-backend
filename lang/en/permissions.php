<?php

use App\Models\Permission;

return [
    // Only for admins
    Permission::CUSTOMERS_VIEW => [
        "label" => "customers:view",
        "title" => "View all customers",
        "description" => [
            "short" => "Allow to see all customers",
            "long" => "Allow to see all customers. Only for admins"
        ]
    ],

    Permission::VOUCHERS_VIEW => [
        "label" => "vouchers:view",
        "title" => "View all vouchers",
        "description" => [
            "short" => "Allow to see all vouchers",
            "long" => "Allow to see all vouchers. Only for admins",
        ]
    ],

    Permission::TRANSACTIONS_VIEW => [
        "label" => "transactions:view",
        "title" => "View all transactions",
        "description" => [
            "short" => "Allow to see all transactions",
            "long" => "Allow to see all transactions. Only for admins.",
        ]
    ],

    Permission::FINANCES_VIEW => [
        "label" => "finances:view",
        "title" => "View any finance request",
        "description" => [
            "short" => "Allow to see all finance requests",
            "long" => "Allow to see all finance requests including archived. Only for admins."
        ]
    ],

    Permission::FINANCES_MANAGEMENT => [
        "label" => "finances:management",
        "title" => "Manage finance requests",
        "description" => [
            "short" => "Approve, reject and make finance requests",
            "long" =>
                "Allow to approve or reject finance request and make request for specific customer. " .
                "Only for admins. It also makes available to see active finance requests.",
        ]
    ],

    Permission::ACTIVITY_VIEW => [
        "label" => "activity:view",
        "title" => "View any activity log",
        "description" => [
            "short" => "Allow to see all activity logs",
            "long" => "Allow to see all activity logs. Only for admins.",
        ]
    ],

    // Only for customers
    Permission::CUSTOMER_VIEW => [
        "label" => "customer:view",
        "title" => "View customer's info",
        "description" => [
            "short" => "Allow to see customer's info (balance)",
            "long" => "Allow to see customer's info (balance).",
        ]
    ],

    Permission::CUSTOMER_USER_VIEW => [
        "label" => "user:view",
        "title" => "View all users",
        "description" => [
            "short" => "Allow to see all users",
            "long" => "Allow to see all users.",
        ]
    ],

    Permission::CUSTOMER_FINANCE => [
        "label" => "finance:management",
        "title" => "Finance management",
        "description" => [
            "short" => "Allow to see, create and delete finance requests",
            "long" => "Allow to see all active and archived finance requests. " .
                    "It also allows to make new requests and delete existing requests",
        ]
    ],

    Permission::CUSTOMER_VOUCHER_VIEW => [
        "label" => "voucher:view",
        "title" => "View any voucher",
        "description" => [
            "short" => "Allow to see all own vouchers",
            "long" => "Allow to see all active and archived vouchers.",
        ]
    ],

    Permission::CUSTOMER_VOUCHER_GENERATE => [
        "label" => "voucher:generate",
        "title" => "Generate voucher",
        "description" => [
            "short" => "Allow to generate voucher",
            "long" => "Allow to generate voucher.",
        ]
    ],

    Permission::CUSTOMER_VOUCHER_REDEEM => [
        "label" => "voucher:redeem",
        "title" => "Redeem voucher",
        "description" => [
            "short" => "Allow to redeem voucher",
            "long" => "Allow to redeem voucher.",
        ]
    ],

    Permission::CUSTOMER_VOUCHER_FREEZE => [
        "label" => "voucher:freeze",
        "title" => "Freeze voucher",
        "description" => [
            "short" => "Allow to freeze/unfreeze own vouchers",
            "long" => "Allow to freeze/unfreeze own vouchers. It also allows to see all own active vouchers",
        ]
    ],

    Permission::CUSTOMER_TRANSACTIONS_VIEW => [
        "label" => "transaction:view",
        "title" => "View any transaction",
        "description" => [
            "short" => "Allow to see all transactions",
            "long" => "Allow to see all active and archived transactions",
        ]
    ],
];
