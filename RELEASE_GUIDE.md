# Kontentainment Events - Release & Versioning Policy

This document outlines the **Mandatory** steps for each plugin release to ensure version consistency and native WordPress update detection.

## 1. Version Governance Policy

Every code change must include a version bump in the main plugin file.

**Targets for update**:

- `kontentainment-events.php` header: `Version: x.x.x`
- `kontentainment-events.php` constant: `define( 'KE_PLUGIN_VERSION', 'x.x.x' );`

**The "Golden Rule"**:

The `plugin header version`, `KE_PLUGIN_VERSION`, `git tag`, and `GitHub Release version` must match **exactly**.

---

## 2. Mandatory Release Workflow

Follow these steps for every single release:

### 1. Version Bump (Pre-Commit)

Update `kontentainment-events.php` with the new version number.

```bash
# Example: Bump to 1.1.2
# 1. Edit File Header to: Version: 1.1.2
# 2. Edit Constant to: define( 'KE_PLUGIN_VERSION', '1.1.2' );
```

### 2. Commit the Bump

Commit the version change to the repository.

```bash
git add .
git commit -m "Global Version Bump to 1.1.2"
git push
```

### 3. Tag and Push Tag

Only create the tag **after** the commit containing the version bump is pushed.

```bash
git tag v1.1.2
git push origin v1.1.2
```

---

## 3. Automated Build Validation

The GitHub Actions workflow (`release.yml`) includes a `Validate Plugin Version` step that will **FAIL** the build if:

1. The Tag (e.g., `v1.1.2`) does not match the Plugin Header Version (`1.1.2`).
2. The Tag does not match the `KE_PLUGIN_VERSION` constant.

**If the validation fails**: No ZIP asset will be attached to the release, and the update will not show up in WordPress.

---

## 4. Manual Verification

After a release, verify:

- Download the `kontentainment-events.zip` from GitHub.
- Unzip and check `kontentainment-events.php`.
- Version should be the NEW version (e.g., `1.1.2`).
- Check `KEA Events > Maintenance Tools` in WordPress to confirm the update Handshake is successful.
