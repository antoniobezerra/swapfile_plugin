<?php

shell_exec('/etc/rc.d/rc.swapfile getplgversions');

$cfgPath = '/boot/config/plugins/swapfile/swapfile.cfg';
$statusPath = '/usr/local/emhttp/plugins/swapfile/swapfile.status';

$defaults = [
    'SWAP_ENABLE_ON_BOOT' => 'false',
    'SWAP_DELETE' => 'false',
    'SWAP_LOCATION' => '/mnt/cache',
    'SWAP_FILENAME' => 'swapfile',
    'SWAP_NAME' => 'UNRAID-SWAP',
    'SWAP_SIZE_MB' => '8192',
];

$swapfile_cfg = array_merge($defaults, is_readable($cfgPath) ? (parse_ini_file($cfgPath) ?: []) : []);
$swapfile_status = is_readable($statusPath) ? (parse_ini_file($statusPath) ?: []) : [];

function h($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function selected($current, $value)
{
    return (string)$current === (string)$value ? ' selected' : '';
}

function commandPath()
{
    return '/plugins/swapfile/scripts/rc.swapfile';
}

$swapfile_location = rtrim($swapfile_cfg['SWAP_LOCATION'], '/');
$swapfile_filename = $swapfile_cfg['SWAP_FILENAME'];
$swapfile_fullpath = $swapfile_location . '/' . $swapfile_filename;
$swapfile_exists = file_exists($swapfile_fullpath);
$swapfile_running = false;
$swapfile_size = 0;
$swapfile_usage = 0;

$swapSummary = trim((string)shell_exec('swapon --show=NAME,SIZE,USED --noheadings 2>/dev/null'));
if ($swapSummary !== '') {
    foreach (explode("\n", $swapSummary) as $line) {
        $columns = preg_split('/\s+/', trim($line));
        if (($columns[0] ?? '') === $swapfile_fullpath) {
            $swapfile_running = true;
            $swapfile_size = $columns[1] ?? '0B';
            $swapfile_usage = $columns[2] ?? '0B';
            break;
        }
    }
}

$disk_free_mb = is_dir($swapfile_location)
    ? trim((string)shell_exec('df -Pm ' . escapeshellarg($swapfile_location) . " 2>/dev/null | awk 'NR==2 {print $4}'"))
    : '';

$plugin_version = $swapfile_status['SWAP_PLG_LOCAL_VER'] ?? 'unknown';
?>

<div style="display:flex; gap:2%; flex-wrap:wrap;">
  <div style="width:49%; min-width:360px;">
    <div id="title">
      <span class="left">Status</span>
    </div>

    <p>
      SwapFile Plugin for Unraid 7+. Updates are managed through the Unraid Plugin Manager or Community Applications.
    </p>

    <table>
      <tr>
        <td>Swap file exists:</td>
        <td>
          <?php if ($swapfile_exists): ?>
            <span class="green-text"><b>&#10004;</b></span>
          <?php else: ?>
            <span class="orange-text"><b>&#10006;</b></span>
          <?php endif; ?>
        </td>
      </tr>
      <tr>
        <td>Swap file in use:</td>
        <td>
          <?php if ($swapfile_running): ?>
            <span class="green-text"><b>&#10004;</b></span>
          <?php else: ?>
            <span class="orange-text"><b>&#10006;</b></span>
          <?php endif; ?>
        </td>
      </tr>
      <tr>
        <td>Configured path:</td>
        <td><b><?= h($swapfile_fullpath); ?></b></td>
      </tr>
      <?php if ($swapfile_running): ?>
      <tr>
        <td>Active size / used:</td>
        <td><b><?= h($swapfile_size); ?></b> / <b><?= h($swapfile_usage); ?></b></td>
      </tr>
      <?php endif; ?>
      <tr>
        <td>Free space at location:</td>
        <td><?= $disk_free_mb !== '' ? h($disk_free_mb . ' MB') : '<span class="orange-text">location not found</span>'; ?></td>
      </tr>
      <tr>
        <td>Plugin version:</td>
        <td><b><?= h($plugin_version); ?></b></td>
      </tr>
    </table>

    <div id="title">
      <span class="left">Actions</span>
    </div>

    <table>
      <tr style="font-weight:bold; color:#333333; background:#F0F0F0; text-shadow:0 1px 1px #FFFFFF;">
        <td>Action</td>
        <td>Description</td>
      </tr>
      <?php if ($swapfile_running): ?>
      <tr>
        <td>
          <form name="stop" method="POST" action="/update.htm" target="progressFrame">
            <input type="hidden" name="cmd" value="<?= h(commandPath()); ?>">
            <input type="hidden" name="arg1" value="stop">
            <input type="submit" name="runCmd" value="Stop">
          </form>
        </td>
        <td>Stop swap usage for the configured file.</td>
      </tr>
      <tr>
        <td>
          <form name="restart" method="POST" action="/update.htm" target="progressFrame">
            <input type="hidden" name="cmd" value="<?= h(commandPath()); ?>">
            <input type="hidden" name="arg1" value="restart">
            <input type="submit" name="runCmd" value="Restart">
          </form>
        </td>
        <td>Stop and start the configured swap file.</td>
      </tr>
      <?php else: ?>
      <tr>
        <td>
          <form name="start" method="POST" action="/update.htm" target="progressFrame">
            <input type="hidden" name="cmd" value="<?= h(commandPath()); ?>">
            <input type="hidden" name="arg1" value="start">
            <input type="submit" name="runCmd" value="Start">
          </form>
        </td>
        <td>Create the file if needed, then enable swap.</td>
      </tr>
      <?php endif; ?>
    </table>
  </div>

  <div style="width:49%; min-width:360px;">
    <div id="title">
      <span class="left">Configuration</span>
    </div>

    <form name="swapfile_settings" method="POST" action="/update.htm" target="progressFrame">
      <table>
        <tr>
          <td colspan="2" align="center">
            <input type="hidden" name="cmd" value="<?= h(commandPath()); ?>">
            <input type="hidden" name="arg1" value="updatecfg">
            <input type="submit" name="runCmd" value="Save Configuration">
            <button type="button" onClick="done();">Return to Settings</button>
          </td>
        </tr>
        <tr style="font-weight:bold; color:#333333; background:#F0F0F0; text-shadow:0 1px 1px #FFFFFF;">
          <td colspan="2">Startup</td>
        </tr>
        <tr>
          <td>Start swap file during array start:</td>
          <td>
            <select name="arg2" id="arg2" size="1">
              <option value="true"<?= selected($swapfile_cfg['SWAP_ENABLE_ON_BOOT'], 'true'); ?>>Yes</option>
              <option value="false"<?= selected($swapfile_cfg['SWAP_ENABLE_ON_BOOT'], 'false'); ?>>No</option>
            </select>
          </td>
        </tr>
        <tr>
          <td>Delete swap file upon stop:</td>
          <td>
            <select name="arg3" id="arg3" size="1">
              <option value="true"<?= selected($swapfile_cfg['SWAP_DELETE'], 'true'); ?>>Yes</option>
              <option value="false"<?= selected($swapfile_cfg['SWAP_DELETE'], 'false'); ?>>No</option>
            </select>
          </td>
        </tr>
        <tr style="font-weight:bold; color:#333333; background:#F0F0F0; text-shadow:0 1px 1px #FFFFFF;">
          <td colspan="2">Swap File</td>
        </tr>
        <tr>
          <td>Location:</td>
          <td><input type="text" name="arg4" id="arg4" style="width:17em;" maxlength="255" value="<?= h($swapfile_cfg['SWAP_LOCATION']); ?>"></td>
        </tr>
        <tr>
          <td>Filename:</td>
          <td><input type="text" name="arg5" id="arg5" style="width:17em;" maxlength="64" value="<?= h($swapfile_cfg['SWAP_FILENAME']); ?>"></td>
        </tr>
        <tr>
          <td>Label:</td>
          <td><input type="text" name="arg6" id="arg6" style="width:17em;" maxlength="32" value="<?= h($swapfile_cfg['SWAP_NAME']); ?>"></td>
        </tr>
        <tr>
          <td>Size in MB:</td>
          <td><input type="text" name="arg7" id="arg7" style="width:6em;" maxlength="10" value="<?= h($swapfile_cfg['SWAP_SIZE_MB']); ?>"> MB</td>
        </tr>
        <tr>
          <td colspan="2">
            Use a persistent disk path such as <b>/mnt/cache</b>. User shares such as <b>/mnt/user</b> are intentionally rejected.
          </td>
        </tr>
      </table>
    </form>
  </div>
</div>
