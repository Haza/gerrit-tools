```
   _____                _ _     _______          _
  / ____|              (_) |   |__   __|        | |    
 | |  __  ___ _ __ _ __ _| |_     | | ___   ___ | |___ 
 | | |_ |/ _ \ '__| '__| | __|    | |/ _ \ / _ \| / __|
 | |__| |  __/ |  | |  | | |_     | | (_) | (_) | \__ \
  \_____|\___|_|  |_|  |_|\__|    |_|\___/ \___/|_|___/
```

Gerrit Tools is a CLI interface that allows you to interact with a Gerrit instance.

## Supported commands

It currently support a limited set of commands : 

**gtools project:list**
List the project you have access to.

**gtools changes:list**
List the open changes you have access to.

**gtools change:detail ```<change_id>```**
- Optional : ```<change_id>``` The change ID you want to pick. 
If not present, it will let you choose a change that is open.
Display more informations about a change.

**gtools change:pick ```<change_id>``` ```--method=<ssh|http>```**
- Optional : ```<change_id>``` The change ID you want to pick. 
If not present, it will let you choose a change that is open.
- Optional : ```--method=<ssh|http>``` the method you prefer to pick the change.

## How to install
Just run ```composer install```
You also need to manually fill a ```~/.gerrittools``` that contains the connection credentials : 
example : 
```
gerrit_uri: http://domain.tld:port
user: username
pass: password
```
This will soon be improved.

For now, that's all folks !
