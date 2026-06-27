# SwapFile Plugin for Unraid 7+

This plugin creates, starts, stops and manages a Linux swap file on an Unraid server.

The maintained fork targets Unraid 7+ and refreshes the original 2015 plugin for the current WebGUI/PHP runtime. It keeps the same core workflow while removing the unsafe in-plugin auto-update behavior.

## Install

In the Unraid WebGUI:

1. Open **Plugins**.
2. Paste this URL into **Install Plugin**:

```text
https://raw.githubusercontent.com/antoniobezerra/swapfile_plugin/master/swapfile.plg
```

3. Open **Settings > SwapFile**.
4. Review the location and size before starting swap.

## Recommended Settings

Use a persistent disk path, not a user share.

Recommended default:

```text
Location: /mnt/cache
Filename: swapfile
Label: UNRAID-SWAP
Size: 8192 MB
Start during array start: No
Delete upon stop: No
```

The plugin intentionally rejects `/mnt/user`, `/mnt/user0`, `/boot`, `/`, empty paths and paths containing `..`.

## Commands

The plugin installs this helper:

```bash
/etc/rc.d/rc.swapfile start
/etc/rc.d/rc.swapfile stop
/etc/rc.d/rc.swapfile restart
/etc/rc.d/rc.swapfile boot
```

Status can be checked with:

```bash
swapon --show
free -h
cat /proc/swaps
```

## Notes

- Swap helps avoid out-of-memory kills, but it is not a replacement for enough RAM.
- Prefer SSD-backed cache storage for swap.
- Updates are handled by Unraid Plugin Manager or Community Applications.
- The original plugin was created by Dan Kessler. This fork keeps attribution while updating compatibility and security posture.

## Support

Open issues at:

```text
https://github.com/antoniobezerra/swapfile_plugin/issues
```
