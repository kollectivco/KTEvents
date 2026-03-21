# Kontentainment Events Release & Update Guide

This plugin supports native WordPress updates via GitHub Releases. Follow these steps to publish a new version.

## 1. Prepare the Code
- Update the `Version` header in `kontentainment-events.php`.
- Update `KE_PLUGIN_VERSION` constant in `kontentainment-events.php`.
- Ensure all changes are committed and pushed to the `main` branch.

## 2. Create a Tag
Create a semantic version tag (e.g., `v1.0.1` or `1.0.1`):
```bash
git tag v1.0.1
git push origin v1.0.1
```

## 3. Automatic Build (GitHub Actions)
Once the tag is pushed, a GitHub Action (`.github/workflows/release.yml`) will:
1. Build a clean distribution folder named `kontentainment-events/`.
2. ZIP the folder into `kontentainment-events.zip`.
3. Create a GitHub Release.
4. Attach `kontentainment-events.zip` to the release.

## 4. WordPress Detection
- WordPress checks for updates every 12 hours.
- To force a check, go to **KE Events > Maintenance Tools** and click **Check for Updates Now**.
- If a newer version is found, an "Update Now" notice will appear in the **Plugins** screen.
- "View details" will show the release notes from GitHub as the changelog.

## 5. Technical Requirements for Updates
- The Root ZIP folder **MUST** be `kontentainment-events/`.
- The plugin uses `site_transient_update_plugins` and `plugins_api` hooks.
- If the repository is private, define `KE_GITHUB_TOKEN` in `wp-config.php` or set it in **KE Events > Settings**.
