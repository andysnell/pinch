# Claude Configuration Directory

This directory contains modularized configuration files for Claude AI assistance on the Pinch Framework project.

## Directory Structure

```
.claude/
├── README.md                   # This file
├── agent-guidelines.md         # Critical guidelines for AI assistance
├── coding-standards.md         # PHP coding standards and conventions
├── commands-reference.md       # Common development commands
├── development-workflow.md     # Development environment and workflow
├── implementation-patterns.md  # Code examples and implementation patterns
├── project-overview.md        # Monorepo and package organization
├── testing-guidelines.md       # Testing requirements and best practices
└── agents/                     # Specialized agent prompt files
    ├── code-reviewer.md
    ├── documentation-specialist.md
    ├── implementation-code-monkey.md
    ├── monorepo-architect.md
    ├── performance-optimizer.md
    ├── quality-assurance-specialist.md
    ├── refactor-code-monkey.md
    └── security-auditor.md
```

## Purpose

By breaking down the configuration into separate files, we achieve:

1. **Better Organization** - Each topic has its own dedicated file
2. **Easier Maintenance** - Updates can be made to specific sections without affecting others
3. **Reduced Repetition** - Common guidelines are referenced rather than duplicated
4. **Improved Readability** - Smaller, focused files are easier to navigate

## File Descriptions

### agent-guidelines.md

Contains critical instructions for AI agents including:

- Proactive agent usage guidelines
- Framework-specific best practices
- Efficiency tips for tool usage

### coding-standards.md

Comprehensive PHP coding standards including:

- General coding principles
- PHP 8.4 feature usage
- Naming conventions
- Type safety requirements
- Service vs Domain class distinctions
- Security considerations
- Framework patterns

### commands-reference.md

Quick reference for common development commands:

- Docker-based PHP execution
- Testing commands
- Code quality tools
- Development utilities

### development-workflow.md

Development environment setup and workflow:

- Docker environment notes
- Development workflow steps
- Request acceptance criteria
- Quality tools and commands
- Development tools overview

### project-overview.md

Detailed monorepo structure documentation:

- Package organization
- Component descriptions
- Namespace conventions
- Directory structure

### testing-guidelines.md

Testing requirements and best practices:

- PHPUnit 12 configuration
- Test organization patterns
- Testing examples
- Fixture guidelines
- Framework-specific testing

### implementation-patterns.md

Code examples and implementation patterns:

- Value object implementation
- Service and service provider patterns
- Configuration and event patterns
- Exception and middleware examples
- Package placement rules

### agents/ directory

Contains specialized agent prompt files that reference the modular configuration:

- Each agent has specific expertise areas
- Agents reference common configurations to avoid duplication
- Agents provide specialized guidance for their domains

## Usage

The main CLAUDE.md file in the project root references these modular files. When working on specific aspects of the
project, refer to the relevant configuration file for detailed guidelines.

## Maintenance

When updating these files:

1. Keep information focused and relevant to the file's topic
2. Avoid duplication across files
3. Update cross-references if file names or sections change
4. Maintain consistent formatting across all files
