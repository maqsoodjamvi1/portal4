# Git Worktree Hygiene

This repository is a legacy CodeIgniter/PHP application with many generated and runtime files nearby. Keep Git signal high by separating source changes from machine-local output.

## Local Git Settings

For this Windows checkout, use:

```powershell
git config core.autocrlf false
git config core.filemode false
```

The repository also has `.gitattributes` so line endings are normalized in Git while local editors can remain predictable.

## Ignore Policy

Runtime folders, backups, logs, local database dumps, dependency folders, and editor files belong outside version control. The root `.gitignore` covers those paths.

Important: ignore rules do not remove files that are already tracked. If a tracked runtime file should leave Git, do that intentionally in a dedicated cleanup commit.

## Reviewing A Large Dirty Tree

Use the summary helper first:

```powershell
powershell -ExecutionPolicy Bypass -File .\tools\git-status-summary.ps1
powershell -ExecutionPolicy Bypass -File .\tools\git-status-summary.ps1 -Buckets
powershell -ExecutionPolicy Bypass -File .\tools\git-status-summary.ps1 -Bucket "02"
powershell -ExecutionPolicy Bypass -File .\tools\git-status-summary.ps1 -Bucket "02" -PathsOnly
powershell -ExecutionPolicy Bypass -File .\tools\git-status-summary.ps1 -Bucket "02" -GitAddCommand
```

Then review real source areas:

```powershell
git status --short -- app public
git diff --stat -- app public
git ls-files --others --exclude-standard
```

Some tracked runtime/vendor files may be marked local-only with Git's skip-worktree bit to keep this checkout usable. Audit those flags with:

```powershell
powershell -ExecutionPolicy Bypass -File .\tools\git-status-summary.ps1 -ShowLocalOnly
```

For the current dirty-tree breakdown and suggested commit buckets, see
`docs/worktree-audit.md`.

## Recommended Cleanup Order

1. Commit repository hygiene changes separately.
2. Review and commit coherent feature groups by module.
3. Remove tracked runtime assets only in a dedicated commit, after confirming they are not deployment fixtures.
4. Keep vendor/system framework updates separate from application changes.
