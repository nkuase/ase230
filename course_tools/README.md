# ASE 230 Tools — Read in This Order

These slides cover the tools HW1 asks you to install and verify. Read
each one before doing the matching install step in `HW1.md`.

1. **`lecture/01. vscode.md`** — install VS Code, confirm you can
   preview Markdown files, connect to WSL2 (Windows only).
2. **`lecture/02. install_ase230_tools_with_wsl2.md`** — **Windows
   only.** Skip this if you're on macOS or Linux.
3. **`lecture/03. php installation.md`** — install and verify PHP.
4. **`lecture/04. mysql installation.md`** — install and verify MySQL.
5. Go back to `HW1.md` and complete the **Verify** steps for each
   item — that's what's actually graded.

**Optional, not required for HW1:** `lecture/VS Code Reference.md` —
general VS Code background (extensions, shortcuts, debugging) beyond
what HW1 needs.

## Windows/WSL2: pick ONE path for Steps 2–4, not both

Step 2's script already installs PHP and MySQL for you, so running it
plus Steps 3 and 4 would install everything twice. Choose one:

- **All-in-one (recommended):** run `code/02.../run.sh` once. You do
  **not** need to separately run the Step 3 and Step 4 scripts.
- **Individually:** skip Step 2's script, then run `code/03.../run.sh`
  and `code/04.../run.sh` on their own.

Mac and native Linux users don't have a Step 2 at all — just run
Steps 3 and 4.

Each slide has a matching `code/<topic>/run.sh` that automates the
install for you (Mac/Linux/WSL2 handled automatically) — see the
`readme.txt` in each `code/<topic>/` folder for what it does and why.

PDF versions of these slides are built with `sh code/build.sh` (see
`marp.ini`) and linked directly from `HW1.md`.

## When something goes wrong

This should work in most cases, but depending on your computer's
specific setup, you might run into an issue (antivirus software,
unusual permissions, a prior broken install, etc.). If that happens,
don't worry — contact or visit the professor and we'll sort it out
together.