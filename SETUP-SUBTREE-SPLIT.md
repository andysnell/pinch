# Pinch Monorepo Subtree-Split Setup Guide

This guide walks you through setting up automated subtree-splitting for the Pinch monorepo, enabling individual packages to be released as separate repositories.

## 🚀 Quick Start: MVP (0.0.0 Release)

### Prerequisites

1. **GitHub Organization Access**: Ensure you have admin access to the `phoneburner` GitHub organization
2. **Personal Access Token**: Create a GitHub PAT with `repo` and `workflow` permissions

### Step 1: Create Target Repositories

Create these repositories in the `phoneburner` organization:

```bash
# All 5 packages for 0.0.0 release
phoneburner/pinch-core
phoneburner/pinch-component
phoneburner/pinch-framework
phoneburner/pinch-phpstan
phoneburner/pinch-template
```

**Repository Settings:**

- **Visibility**: Public
- **Initialize**: Empty (no README, .gitignore, or license)
- **Branch Protection**: Enable for `main` branch after first push

### Step 2: Configure GitHub Secrets

Add these secrets to the main `phoneburner/pinch` repository:

1. **`ACCESS_TOKEN`**: Your GitHub Personal Access Token
    - Scope: `repo`, `workflow`, `write:packages`
    - Used for: Creating releases, pushing to split repositories

### Step 3: Test the Workflow

1. **Manual Test Run**:

    ```bash
    # Go to Actions tab in GitHub
    # Select "Release Monorepo" workflow
    # Click "Run workflow"
    # Enter version: 0.0.0
    # Click "Run workflow" button
    ```

2. **Expected Outcome**:
    - Monorepo tagged with `v0.0.0`
    - All 5 packages split to separate repositories
    - Each split repository tagged with `v0.0.0`
    - GitHub releases created for all 5 repositories

### Step 4: Verify Split Packages

Check that each split repository contains:

- ✅ Package-specific files (composer.json, src/, etc.)
- ✅ Proper composer.json with correct dependencies
- ✅ Git history preserved from monorepo
- ✅ Tagged release `v0.0.0`

## 📊 Implementation Options

### Option 1: MVP (Current Setup)

- **Target**: 2-4 hours setup
- **Features**: Manual triggers, all 5 packages, basic releases
- **Best For**: Quick 0.0.0 release, proof of concept

### Option 2: Production-Ready

- **Target**: 1-2 days setup
- **Features**: Automatic triggers, all 5 packages, semantic versioning
- **Upgrades**:
    - Automatic splitting on tag creation
    - Independent versioning per package
    - Advanced error handling
    - Integration testing

### Option 3: Enterprise-Grade

- **Target**: 3-5 days setup
- **Features**: Path-based triggers, rollback capabilities, monitoring
- **Upgrades**:
    - Selective building based on changed files
    - Automated Packagist updates
    - Dependency validation
    - Advanced monitoring and alerting

## 🔧 Troubleshooting

### Common Issues

**1. Permission Denied**

```
Error: Resource not accessible by integration
```

**Solution**: Check that `ACCESS_TOKEN` has correct permissions and organization access.

**2. Repository Not Found**

```
Error: Not Found (404)
```

**Solution**: Ensure target repositories exist and are accessible by the token.

**3. Split Contains No Changes**

```
Warning: No changes to split
```

**Solution**: This is normal for first run or when packages haven't changed.

**4. Version Conflicts**

```
Error: Tag already exists
```

**Solution**: Use a different version number or delete existing tags.

### Debug Commands

```bash
# Check monorepo structure
./vendor/bin/monorepo-builder validate

# Test split locally (requires splitsh-lite)
splitsh-lite --prefix=packages/core --target=/tmp/core-split

# Validate composer files
composer validate --no-check-publish packages/*/composer.json
```

## 🎯 Next Steps

### Immediate (Post-MVP)

1. **Automatic Triggers**: Add tag-based triggers for production
2. **Documentation**: Update package READMEs in split repositories
3. **Testing**: Add integration tests for split packages

### Medium Term

1. **Semantic Versioning**: Implement conventional commits
2. **Branch Protection**: Add rules to split repositories
3. **Packagist Integration**: Automatic package registration
4. **Monitoring**: Add workflow notifications

### Long Term

1. **Path-Based Splitting**: Only split changed packages
2. **Dependency Validation**: Ensure split packages work independently
3. **Rollback System**: Automated rollback on split failures
4. **Advanced Versioning**: Independent package versioning

## 📚 Resources

- [Symplify MonorepoBuilder Docs](https://github.com/symplify/monorepo-builder)
- [GitHub Actions Matrix Strategy](https://docs.github.com/en/actions/using-jobs/using-a-matrix-for-your-jobs)
- [Semantic Versioning](https://semver.org/)
- [Conventional Commits](https://www.conventionalcommits.org/)

## 🤝 Support

For issues with this setup:

1. Check the GitHub Actions logs in the repository
2. Review the troubleshooting section above
3. Create an issue in the main repository with logs and error details
