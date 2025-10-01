<?php
/**
 * Calendar Dashboard Widget
 * 
 * Shows today's events on the main dashboard
 * Integrates with existing dashboard layout
 * 
 * @author CRM Framework
 * @version 1.0
 */

// This file should be included in dashboard.php
// Assumes $sessions and $user_id are already available

require_once CLASSES . 'Models/CalendarEvent.php';

$calendar = new CalendarEvent();
$todays_events = $calendar->getTodaysEvents($user_id, 5);
$event_stats = $calendar->getEventStats($user_id);
?>

<!-- Today's Calendar Events Widget -->
<div class="col-md-6 mb-4">
    <div class="card h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="fas fa-calendar-day me-2 text-primary"></i>Today's Events
            </h5>
            <a href="calendar/" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-calendar-alt me-1"></i>View Calendar
            </a>
        </div>
        <div class="card-body">
            <?php if (empty($todays_events)): ?>
                <div class="text-center text-muted py-3">
                    <i class="fas fa-calendar-check fa-3x mb-3 opacity-50"></i>
                    <p class="mb-0">No events scheduled for today</p>
                    <a href="calendar/" class="btn btn-sm btn-primary mt-2">
                        <i class="fas fa-plus me-1"></i>Add Event
                    </a>
                </div>
            <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($todays_events as $event): ?>
                        <div class="list-group-item px-0 py-2 border-0">
                            <div class="d-flex align-items-start">
                                <div class="flex-shrink-0 me-3">
                                    <i class="<?= htmlspecialchars($event['event_type_icon'] ?? 'fas fa-calendar') ?> text-primary"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1"><?= htmlspecialchars($event['title']) ?></h6>
                                            <small class="text-muted">
                                                <i class="fas fa-clock me-1"></i>
                                                <?= date('g:i A', strtotime($event['start_datetime'])) ?>
                                                <?php if ($event['end_datetime']): ?>
                                                    - <?= date('g:i A', strtotime($event['end_datetime'])) ?>
                                                <?php endif; ?>
                                            </small>
                                            <?php if ($event['contact_name'] || $event['company_name']): ?>
                                                <br>
                                                <small class="text-muted">
                                                    <i class="fas fa-user me-1"></i>
                                                    <?= htmlspecialchars($event['contact_name'] ?: $event['company_name']) ?>
                                                </small>
                                            <?php endif; ?>
                                            <?php if ($event['location']): ?>
                                                <br>
                                                <small class="text-muted">
                                                    <i class="fas fa-map-marker-alt me-1"></i>
                                                    <?= htmlspecialchars($event['location']) ?>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-end">
                                            <?php if ($event['priority'] >= 8): ?>
                                                <span class="badge bg-danger">High Priority</span>
                                            <?php elseif ($event['priority'] >= 6): ?>
                                                <span class="badge bg-warning">Medium Priority</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <?php if (count($todays_events) >= 5): ?>
                    <div class="text-center mt-3">
                        <a href="calendar/" class="btn btn-sm btn-outline-primary">
                            View All Events
                        </a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        
        <!-- Quick Stats Footer -->
        <div class="card-footer bg-light">
            <div class="row text-center">
                <div class="col-3">
                    <div class="text-primary fw-bold"><?= $event_stats['phone_calls'] ?? 0 ?></div>
                    <small class="text-muted">Calls</small>
                </div>
                <div class="col-3">
                    <div class="text-success fw-bold"><?= $event_stats['emails'] ?? 0 ?></div>
                    <small class="text-muted">Emails</small>
                </div>
                <div class="col-3">
                    <div class="text-warning fw-bold"><?= $event_stats['meetings'] ?? 0 ?></div>
                    <small class="text-muted">Meetings</small>
                </div>
                <div class="col-3">
                    <div class="text-danger fw-bold"><?= $event_stats['high_priority'] ?? 0 ?></div>
                    <small class="text-muted">High Priority</small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-refresh widget every 5 minutes
setInterval(function() {
    // Only refresh if we're still on the dashboard
    if (window.location.pathname.includes('dashboard')) {
        location.reload();
    }
}, 300000); // 5 minutes
</script>