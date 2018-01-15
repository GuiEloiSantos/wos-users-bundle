UsersBundle
==

The MemberPoint Whole-Of-Sports (WOS) **UsersBundle** provides functionality
for to the management of user accounts.

Installation
--

**These instructions assume the consuming application uses Symfony Flex.**

Add the following repository to the application's `composer.json` file:

```json
"repositories": [
    {
        "type": "vcs",
        "url":  "git@bitbucket.org:dblsolutions/wos-common-bundle.git"
    },
    {
        "type": "vcs",
        "url":  "git@bitbucket.org:dblsolutions/wos-users-bundle.git"
    }
]
```

Open a command console, enter the project directory, and execute:

```console
$ composer require memberpoint/wos-users-bundle:dev-master
```
