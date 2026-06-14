<?php
/**
 * Role hint for dashboard section ordering (teacher | finance | principal | default).
 *
 * @var string $dashboardLayoutRole
 */
$dashboardLayoutRole = $dashboardLayoutRole ?? 'default';
?>
<div class="d-none" data-dashboard-layout="<?= esc($dashboardLayoutRole, 'attr') ?>" aria-hidden="true"></div>
