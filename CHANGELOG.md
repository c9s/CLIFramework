Version 2.8.1

Several bug fixes:

- class loader generator.
- table component column width calculation.
- fix archive command app-boostrap option

Version 2.8.0

- Added debug tools.
- Fix table component bug.

Version 2.7.2

- Merged Commit bc318ab: Merge pull request #85 from marcioAlmada/fix/compile

   Fix Compile Command

Version 2.7.0

- Rewrite the whole compile command with composer.json support.

Version 2.6.3

- Use universal package that is newer than 1.4

Version 2.6.2

- Fixed column header width checking

Version 2.6.1

- Fixed column width for empty cell.

Version 2.6

- Added CommandExtension support. @shinnya++
- Added Event system. (Universal/Event/PhpEvent)

Version 2.5

- Added ANSI Color definition class.
- Added show/hide cursor ansi code to CursorControl.
- Moved all singleton object builders to `CLIFramework\ServiceContainer`.
- Added more logger methods.
- Added powerful text table generator.
- Added `compile` command to compile console application into phar file.
- #64 - Abstract IO layers (stdin/stdout, tty) by @shinnya++
- #66 - Support password prompt by @shinnya++
- #65 - Global configuration file support by @shinnya++
- #63 - Add logException method support by @shinnya++
- #51 - New correct: use `similar_text` instead of `levenshtein` by @dh3014++
- #49 - Command autoloading feature by @dh3014++
- #10 - Allow raw output even if terminal emulator support colors by @marcioAlmada++
- Several bugfixes by @marcioAlmada++

Version 2.2   - Wed Dec 31 11:27:50 2014

- Added ConsoleInfo classes for detecting the dimension of the console.

Version 1.7   - Mon Jun 30 11:41:33 2014

- Added zsh completion generator.
- Added ArgInfo class.
- Added arginfo() method to command class.
- Improved help information generator.

Version 1.3.1 - 三  3/14 18:10:37 2012

- Added Chooser component.
- Added Prompter component.
- Refactor formatter methods.

