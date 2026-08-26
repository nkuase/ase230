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
- Starts the MySQL server and checks it actually works, not just
  that it's installed.
- Same result either way — if you'd rather follow
  "pdf/04. mysql installation.pdf" yourself, that works too. This script
  is a shortcut, not a requirement.

How to use it
--------------
1. Open a terminal (VS Code's integrated terminal is fine:
   Ctrl+` / Cmd+`). Windows users: use your WSL2 terminal, not
   PowerShell/cmd.
2. Navigate into this folder:
       cd "code/04. mysql installation"
3. Run it:
       bash run.sh

What it does
------------
- Detects whether you're on macOS, WSL2, or Linux. The Linux helper
  supports Ubuntu and other distributions that provide `apt`.
- Installs MySQL and starts the server (using `service`, not
  `systemctl`, on WSL2 — see the slide for why).
- Runs a real `SELECT VERSION();` query at the end to confirm the
  server actually accepts connections, not just that it's running.

If something goes wrong
------------------------
This should work in most cases, but depending on your computer's
specific setup, you might run into an issue (antivirus software,
unusual permissions, a prior broken install, etc.). If that happens,
don't worry — contact or visit the professor and we'll sort it out
together.
