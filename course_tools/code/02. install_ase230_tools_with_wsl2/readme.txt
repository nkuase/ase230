What is this folder?
=====================

`run.sh` is a script — a plain text file listing the same commands
you would otherwise type into your terminal one by one. Running it
does all of them for you in one step, in the right order, so you
don't have to copy/paste each line from the slide yourself.

This one is for Windows users only (inside WSL2). If you're on Mac
or Linux, you don't need this folder at all.

Why use it?
-----------
- Faster: one command installs PHP, MySQL, and Composer together,
  instead of running each install command by hand.
- Fewer typos: nothing to mistype or paste wrong.
- Same result either way — if you'd rather type the commands from
  "02. install_ase230_tools_with_wsl2.md" yourself, that works too.
  This script is a shortcut, not a requirement.

How to use it
--------------
1. Open your WSL2 (Ubuntu) terminal.
2. Navigate into this folder. It has spaces in its name, so quote
   the path or use Tab-completion:
       cd "ase230/course_tools/code/02. install_ase230_tools_with_wsl2"
3. Run it:
       bash run.sh

What it does
------------
- Installs `dos2unix` first (fixes a common Windows-vs-Linux file
  problem, see the slide for details).
- Adds the PHP package repository and installs PHP 8.3+ with the
  extensions this course uses.
- Installs MySQL and starts the server (required — HW1 checks that
  MySQL actually runs).
- Installs Composer, the PHP package manager, verifying it's the
  genuine installer before running it.

If something goes wrong
------------------------
This should work in most cases, but depending on your computer's
specific setup, you might run into an issue (antivirus software,
unusual permissions, a prior broken install, etc.). If that happens,
don't worry — contact or visit the professor and we'll sort it out
together.
