PREFACE:
A lot of people is using password manager that allows you to access your password from different devices using one single password.
It's much better than writing on yellow notes stiched to your screen, anyway you have to trust the company making your app,
because they could intercept easily your password and have access to your whole online life.
I do not trust anyone about my passwords and since I use a different password for every system, I had to write a strong password manager
for my personal usage. The security is high and the clear source code guarantees that there are no backdoors.


DESCRIPTION:
The encryption is done by 3 layers of encryption: AES 256, Camellia 256 and Chacha20 with a key lenght of 768 bits. Clear data is never
saved on disk and you check well the short source code.
pwdlandi.php does not work on a graphical user interface, because much more components are involved in the security controls, instead I opted
for a simple command line interface.

PRE-REQUIREMENTS:
PHP 7.X and openssl library for PHP

INSTALLATION:
Download  pwdlandi.php somewhere and...
php pwdlandi.php
will work everywhere

For other info, edit and check on top of the source code.

For any help, please drop a message to samuele@landi.ae
