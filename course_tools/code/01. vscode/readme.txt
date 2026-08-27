What is this folder?
=====================

`run.sh` is a script — a plain text file listing the same commands
you would otherwise type into your terminal one by one. Running it
does all of them for you in one step, in the right order, so you
don't have to copy/paste each line from the slide yourself.

Why use it?
-----------
- Faster: one command instead of many.
- Fewer typos: nothing to mistype or paste wrong.
- Same result either way — if you'd rather follow
  "pdf/01. vscode.pdf" yourself, that works too. This script is a
  shortcut, not a requirement.

How to use it
--------------
1. Open a terminal (VS Code's integrated terminal is fine:
   Ctrl+` / Cmd+`).
2. Navigate into this folder:
       cd "code/01. vscode"
3. Run it:
       bash run.sh

What it does
------------
- Checks whether the `code` command (VS Code's CLI) is already
  available.
- On Mac: installs VS Code automatically via Homebrew if it's
  missing.
- On WSL2: VS Code itself has to be installed on the Windows side
  (not inside Linux), so the script just tells you what's missing
  and points you to the right steps — it can't install it for you
  from here.
- On Ubuntu Linux: installs VS Code with Snap when Snap is available.
- Prints the HW1 Markdown-preview verification step. PHP extensions
  are optional and are not required for HW1.

If something goes wrong
------------------------
This should work in most cases, but depending on your computer's
specific setup, you might run into an issue (antivirus software,
unusual permissions, a prior broken install, etc.). If that happens,
don't worry — contact or visit the professor and we'll sort it out
together.
