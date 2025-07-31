# Architectural Dependency Rules

## Overview

This package provides PHPStan rules to enforce the Pinch framework's dependency hierarchy:

```
core → component → framework
```

## Rules Included

### 1. NoCoreToComponentDependencyRule

Prevents core package classes from depending on component package classes through:

- Extending component classes
- Implementing component interfaces
- Using component traits

### 2. NoComponentToFrameworkDependencyRule

Prevents component package classes from depending on framework package classes through:

- Extending framework classes
- Implementing framework interfaces
- Using framework traits

### 3. DependencyHierarchyRule

Comprehensive rule that checks:

- Use statements (imports)
- Class instantiations (`new`)
- Static method calls

## Installation

Add the architecture rules to your `phpstan.neon` configuration:

```neon
includes:
    - vendor/phoneburner/pinch-phpstan/rules/architecture.neon
```

## Error Messages

When violations are detected, you'll see messages like:

```
Core package class PhoneBurner\Pinch\String\TestClass imports component package class
PhoneBurner\Pinch\Component\Cryptography\ConstantTime. This violates the dependency
hierarchy (core → component → framework).
```

## Fixing Violations

When you encounter a violation, consider:

1. **Moving the code** - If a core class needs component functionality, perhaps that code belongs in the component package
2. **Creating abstractions** - Define interfaces in core that component can implement
3. **Extracting shared code** - Pull common functionality down to the appropriate lower level

## Excluding Legacy Code

If you have legacy violations that can't be fixed immediately, you can exclude them:

```neon
parameters:
    ignoreErrors:
        -
            message: '#Core package class .* imports component package class#'
            path: src/Legacy/SomeFile.php
```

## Testing

The rules include comprehensive test coverage. Run tests with:

```bash
vendor/bin/phpunit --testsuite=phpstan
```
