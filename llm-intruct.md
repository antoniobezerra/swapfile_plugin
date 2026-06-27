# LLM maintenance instructions

This repository is an Unraid plugin fork for managing a swap file. Do not install the plugin on a live Unraid host while making repository updates unless the maintainer explicitly asks for installation/testing.

## Current target

- Repository: `antoniobezerra/swapfile_plugin`
- Branch: `master`
- Supported Unraid version: `7.0.0+`
- Current plugin version: `2026.06.27`
- Main install file: `swapfile.plg`
- Packaged payload: `swapfile-package-2026.06.27.tar.gz`

## Important behavior

- The WebGUI page calls `/plugins/swapfile/scripts/rc.swapfile` through Unraid `/update.htm`.
- The runtime helper must preserve these commands: `start`, `stop`, `restart`, `boot`, `writecfg`, `getplgversions`, `updatecfg`.
- Plugin updates are managed by Unraid Plugin Manager / Community Applications. Do not reintroduce remote self-update logic from the old upstream plugin.
- Config is persisted at `/boot/config/plugins/swapfile/swapfile.cfg`.
- Runtime files install under `/usr/local/emhttp/plugins/swapfile`.

## Safety rules

- Never allow swap files on `/mnt/user`, `/mnt/user0`, `/boot`, `/`, empty paths, or paths containing `..`.
- Keep shell variables quoted.
- Keep swap file permissions at `0600`.
- Keep plugin files packaged with normal permissions: directories `0755`, scripts/events `0755`, read-only assets `0644`.
- Use `fallocate` when available, with `dd` fallback.
- Check free space before creating the swap file.

## Release checklist

1. Update files in `Resources/`.
2. Run syntax checks:

```bash
bash -n Resources/usr/local/emhttp/plugins/swapfile/scripts/rc.swapfile
xmllint --noout swapfile.plg
```

3. Rebuild the package:

```bash
tar --owner=0 --group=0 -czf swapfile-package-YYYY.MM.DD.tar.gz -C Resources usr
```

4. Update `packageMD5` in `swapfile.plg`.
5. Validate `plugins/swapfile.xml` has the same `PluginURL` as `swapfile.plg`.
6. Commit and push.
7. Submit/update the plugin entry for Community Applications.
