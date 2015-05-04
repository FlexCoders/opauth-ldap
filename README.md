Opauth-Ldap
=============
[Opauth][1] strategy for Ldap authentication.

Opauth is a multi-provider authentication framework for PHP.

Getting started
----------------
1. Install Opauth-Ldap:
   ```bash
   cd path_to_opauth/Strategy
   git clone git://github.com/flexcoders/opauth-ldap.git ldap
   ```

2. Configure Opauth-Ldap strategy.

3. ** HOW TO CALL? **


Strategy configuration
----------------------

Required parameters:

```php
<?php
'Ldap' => array(
    'server'        => 'ldap.forumsys.com',
    'port'          => 389,
    'bind-cn'       => 'uid=$username$',
    'bind-dn'       => 'dc=example,dc=com',
    'bind-password' => '$password$',
    'attributes'    => array(
        'uid'      => 'uidnumber',
        'name'     => 'cn',
        'email'    => 'mail',
        'username' => 'uid',
    ),
    'options'       => array(
        LDAP_OPT_PROTOCOL_VERSION => 3,
        LDAP_OPT_REFERRALS => 0,
    ),
    'expiry'        => 86400,
)
```

Optional parameters:
`expire`, `options`

The bind() happens on the concatenation of 'bind-cn' and 'bind-dn', and the 'bind-password'.

Make sure the attributes array contains the correct attribute mapping. On the left side are
the names the Strategy is expecting. If in your schema they are called different, use the
name you are using on the righthand side.

As in this example, we require an attribute called 'name', which in the schema is called 'cn'.
Same for 'email' which is called 'mail' in the schema of the test server.

References
----------
If you want to test using an LDAP server on the internet, you can use the config
given above. More info about the LDAP schema used and the test server objects, see
http://www.forumsys.com/tutorials/integration-how-to/ldap/online-ldap-test-server/

License
---------
Opauth-Ldap is MIT Licensed
Copyright © 2015 FlexCoders Ltd (http://flexcoders.co.uk)

[1]: https://github.com/opauth/opauth
