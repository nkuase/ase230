What is this folder?
=====================

`run.sh` is a script — a plain text file listing the same commands
you would otherwise type into your terminal one by one. Running it
does all of them for you in one step, in the right order, so you
don't have to copy/paste each line from the slide yourself.

Why use it?
-----------
- Handles macOS and Ubuntu/WSL2 differences automatically — you don't
  need to figure out which commands apply to your computer.
- Fewer typos: nothing to mistype or paste wrong.
- Same result either way — if you'd rather follow
  "pdf/03. php installation.pdf" yourself, that works too. This script is
  a shortcut, not a requirement.

How to use it
--------------
1. Open a terminal (VS Code's integrated terminal is fine:
   Ctrl+` / Cmd+`). Windows users: use your WSL2 terminal, not
   PowerShell/cmd.
2. Navigate into this folder:
       cd "code/03. php installation"
3. Run it:
       bash run.sh

What it does
------------
- Detects whether you're on macOS, WSL2, or Linux. The Linux helper
  supports Ubuntu and other distributions that provide `apt`.
- Installs PHP plus the extensions this course uses (curl,
  mbstring, xml, zip), along with the `curl` command needed to
  download Composer safely.
- Installs Composer, the PHP package manager, verifying it's the
  genuine installer before running it (skips this if Composer is
  already installed).
- Runs `php -r 'echo "PHP works\n";'` at the end to confirm PHP
  actually executes, not just that it's installed.

If something goes wrong
------------------------
This should work in most cases, but depending on your computer's
specific setup, you might run into an issue (antivirus software,
unusual permissions, a prior broken install, etc.). If that happens,
don't worry — contact or visit the professor and we'll sort it out
together.
