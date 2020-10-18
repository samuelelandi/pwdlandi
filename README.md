PREFACE:
A lot of people is using password manager that allows you to access your password from different devices using one single password.
It's better than writing on yellow notes stiched to your screen, anyway you have to trust the company giving your the app,
because they could intercept easily your password and have access to your whole life.
I do not trust anyone about my passwords and since I use a different password for every system, I had to write a strong password manager
for my personal usage. 


DESCRIPTION:
The encryption is done by 3 layers of encryption: AES 256, Camellia 256 and Chacha20 with a key lenght of 768 bits. Clear data is never
saved on disk and you check well the short source code.
pwdlandi.php does not work on a graphical user interface, because much more components are involved in the security conntrols and I opted
for a simple command line interface.

PRE-REQUIREMENTS:
PHP 7.X and openssl library for PHP

INSTALLATION:
Download  pwdlandi.php somewhere and...
php pwdlandi.php
will work everywhere

For other info, check the source code:
//***************************************************************************
//**** PASSWORD MANAGER ver 1.1
//**** Features:
//**** 3 layers of encryption: AES 256, Camellia 256 and Chacha
//**** Clear data is never saved on the disk
//**** No cache
//**** short source code easy to check for security
//**** db saved in the current folder as pwd.encrypted
//**** db backup at every rewrite as pwd.encrypted.backup
//**** no graphical interface
//**** Requirements:
//**** - PHP 7
//**** - OPENSSL library for PHP
//****
//**** Tested on:
//**** Mac Os/x 13.4 (High Sierra), requirement are installed by default.
//**** It should work  on Linux and Windows as well.
//***************************************************************************

For any help, please drop a message to samuele@landi.ae
