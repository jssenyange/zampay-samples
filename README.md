zampay-samples
==============

ZAMPAY Samples illustrating connections to the gateway

The samples here illustrate how to connect to the payment gateway.

They can't be used in production directly, but can be used as a knowledge base to build you integration.

PHP
==============

To see sample zampay express usage, set $UseExpress = true in zampayfunctions.php

To see sample API usage, set $UseExpress = false in zampayfunctions.php

The file mpn_notify.php illustrates receiving a mobile payment notification and validating it.

The file mdt_notify.php illustrates receiving a mobile data transfer when user has complete payment and is redirected back to the return_url


