# Contributing to FluentCRM Conditional Status

Thank you for considering contributing to FluentCRM Conditional Status! This document outlines the process for contributing to this project.

## Code of Conduct

Please be respectful and considerate of others. We want this to be a welcoming community for everyone.

## How Can I Contribute?

### Reporting Bugs

Before creating bug reports, please check the existing issues to avoid duplicates. When creating a bug report, include:

- **Clear title and description**
- **WordPress version**
- **PHP version**
- **FluentForms version**
- **FluentCRM version**
- **Steps to reproduce**
- **Expected behavior**
- **Actual behavior**
- **Screenshots** (if applicable)
- **Error logs** (if applicable)

### Suggesting Enhancements

Enhancement suggestions are tracked as GitHub issues. When creating an enhancement suggestion, include:

- **Clear title and description**
- **Use case** - why would this be useful?
- **Proposed solution** - how might it work?
- **Alternatives considered**

### Pull Requests

1. **Fork the repository**
2. **Create a feature branch** (`git checkout -b feature/amazing-feature`)
3. **Make your changes**
4. **Follow coding standards** (see below)
5. **Test thoroughly**
6. **Commit with clear messages** (`git commit -m 'Add some amazing feature'`)
7. **Push to your fork** (`git push origin feature/amazing-feature`)
8. **Open a Pull Request**

## Development Setup

```bash
# Clone the repository
git clone https://github.com/yourusername/fluentcrm-conditional-status.git
cd fluentcrm-conditional-status

# Install dependencies (for code quality tools)
composer install

# Run code sniffer
./vendor/bin/phpcs

# Fix coding standards automatically
./vendor/bin/phpcbf
```

## Coding Standards

This project follows [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/).

### PHP Standards

- Use WordPress PHP Coding Standards
- All code must pass PHPCS with WordPress ruleset
- Use meaningful variable and function names
- Add inline comments for complex logic
- Use proper PHPDoc blocks

### JavaScript Standards

- Follow WordPress JavaScript Coding Standards
- Use ES6+ features where appropriate
- Add JSDoc comments

### General Guidelines

- **Indentation**: Use tabs for indentation
- **Line Length**: Try to keep lines under 100 characters
- **Naming Conventions**: Use WordPress naming conventions
  - Functions: `my_function_name()`
  - Classes: `My_Class_Name`
  - Constants: `MY_CONSTANT_NAME`
  - Variables: `$my_variable_name`

### Documentation

- Add PHPDoc blocks for all functions and classes
- Keep README.md and readme.txt in sync
- Update CHANGELOG.md for all changes
- Add inline comments for complex logic

## Testing

Before submitting a PR, please test:

1. **Fresh Installation**: Install on a clean WordPress site
2. **Plugin Activation**: Ensure no errors on activation
3. **Feed Configuration**: Test the UI in feed settings
4. **Form Submission**: Test with actual form submissions
5. **Different Field Types**: Test checkboxes, radios, selects, text fields
6. **Status Changes**: Verify statuses are applied correctly
7. **Double Opt-in**: Test that "Pending" status triggers double opt-in
8. **Error Handling**: Test with invalid configurations

## Git Commit Messages

- Use the present tense ("Add feature" not "Added feature")
- Use the imperative mood ("Move cursor to..." not "Moves cursor to...")
- Limit the first line to 72 characters or less
- Reference issues and pull requests liberally after the first line

Examples:
```
Add conditional status field selector

- Add dropdown to select form field
- Filter fields by supported types
- Add tooltips for clarity

Fixes #123
```

## Version Numbering

We use [Semantic Versioning](https://semver.org/):

- **MAJOR** version for incompatible API changes
- **MINOR** version for backwards-compatible functionality additions
- **PATCH** version for backwards-compatible bug fixes

## Release Process

1. Update version number in:
   - `fluentcrm-conditional-status.php` (plugin header and constant)
   - `readme.txt` (Stable tag)
   - `CHANGELOG.md`
2. Create a git tag: `git tag -a 1.0.1 -m "Version 1.0.1"`
3. Push tags: `git push --tags`
4. Create GitHub release with changelog notes
5. Submit to WordPress.org (if applicable)

## Questions?

If you have questions about contributing, feel free to:
- Open an issue on GitHub
- Contact the maintainer at your-email@example.com

Thank you for contributing! 🎉
