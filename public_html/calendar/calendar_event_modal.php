<?php
<!-- Event Modal -->
<!-- <div class="modal fade"
     id="eventModal"
     tabindex="-1"
     aria-labelledby="eventModalLabel"
     aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"
            id="eventModalLabel">Add New Event</h5>
        <button type="button"
                class="btn-close"
                data-bs-dismiss="modal"
                aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="eventForm">
          <input type="hidden"
                 id="event_id"
                 name="event_id">
          <input type="hidden"
                 id="csrf_token"
                 name="csrf_token"
                 value="<?php // echo htmlspecialchars($csrf_token); ?>">

<div class="row">
  <div class="col-md-8">
    <div class="mb-3">
      <label for="title"
             class="form-label">Event Title *</label>
      <input type="text"
             class="form-control"
             id="title"
             name="title"
             required>
    </div>
  </div>
  <div class="col-md-4">
    <div class="mb-3">
      <label for="event_type"
             class="form-label">Event Type *</label>
      <select class="form-select"
              id="event_type"
              name="event_type"
              required>
        <?php // foreach ($event_types as $type): ?>
        <option value="<?php // echo $type['id'] ?>"
                data-color="<?php //echo $type['color'] ?>">
          <?php // echo htmlspecialchars($type['name']) ?>
        </option>
        <?php // endforeach; ?>

      </select>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-6">
    <div class="mb-3">
      <label for="start_datetime"
             class="form-label">Start Date & Time *</label>
      <input type="datetime-local"
             class="form-control"
             id="start_datetime"
             name="start_datetime"
             required>
    </div>
  </div>
  <div class="col-md-6">
    <div class="mb-3">
      <label for="end_datetime"
             class="form-label">End Date & Time</label>
      <input type="datetime-local"
             class="form-control"
             id="end_datetime"
             name="end_datetime">
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-6">
    <div class="mb-3">
      <label for="priority"
             class="form-label">Priority (1-10)</label>
      <select class="form-select"
              id="priority"
              name="priority">
        <?php // foreach ($priorities as $priority): ?>
        <option value="<?php // echo $priority['id'] ?>"
                <?php // echo $priority['id'] == 5 ? 'selected' : '' ?>>
          <?php // echo $priority['id'] ?> - <?php // echo htmlspecialchars($priority['name']) ?>
        </option>
        <?php // endforeach; ?>
      </select>
    </div>
  </div>
  <div class="col-md-6">
    <div class="mb-3">
      <label for="status"
             class="form-label">Status</label>
      <select class="form-select"
              id="status"
              name="status">
        <option value="1"
                selected>Pending</option>
        <option value="2">Completed</option>
        <option value="3">Cancelled</option>
        <option value="4">In Progress</option>
      </select>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-6">
    <div class="mb-3">
      <label for="lead_id"
             class="form-label">Related Lead</label>
      <select class="form-select"
              id="lead_id"
              name="lead_id">
        <option value="">Select Lead (Optional)</option>
        -->
        <!-- Will be populated via AJAX -->
        <!--                                 </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label for="contact_id"
                       class="form-label">Related Contact</label>
                <select class="form-select"
                        id="contact_id"
                        name="contact_id">
                  <option value="">Select Contact (Optional)</option>
                  -->
        <!-- Will be populated via AJAX -->
        <!--               </select>
              </div>
            </div>
          </div>

          <div class="mb-3">
            <label for="location"
                   class="form-label">Location</label>
            <input type="text"
                   class="form-control"
                   id="location"
                   name="location"
                   placeholder="Meeting location, phone number, etc.">
          </div>

          <div class="mb-3">
            <label for="description"
                   class="form-label">Description</label>
            <textarea class="form-control"
                      id="description"
                      name="description"
                      rows="3"></textarea>
          </div>

          <div class="mb-3">
            <label for="notes"
                   class="form-label">Notes</label>
            <textarea class="form-control"
                      id="notes"
                      name="notes"
                      rows="2"></textarea>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label for="reminder_minutes"
                       class="form-label">Reminder (minutes before)</label>
                <select class="form-select"
                        id="reminder_minutes"
                        name="reminder_minutes">
                  <option value="">No reminder</option>
                  <option value="5">5 minutes</option>
                  <option value="15"
                          selected>15 minutes</option>
                  <option value="30">30 minutes</option>
                  <option value="60">1 hour</option>
                  <option value="1440">1 day</option>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <div class="form-check mt-4">
                  <input class="form-check-input"
                         type="checkbox"
                         id="all_day"
                         name="all_day">
                  <label class="form-check-label"
                         for="all_day">
                    All Day Event
                  </label>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button"
                class="btn btn-secondary"
                data-bs-dismiss="modal">Cancel</button>
        <button type="button"
                class="btn btn-danger"
                id="deleteEventBtn"
                style="display: none;">Delete</button>
        <button type="button"
                class="btn btn-primary"
                id="saveEventBtn">Save Event</button>
      </div>
    </div>
  </div>
</div>
                  -->
        <!-- Event Detail Modal -->
        <!-- <div class="modal fade"
     id="eventDetailModal"
     tabindex="-1"
     aria-labelledby="eventDetailModalLabel"
     aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"
            id="eventDetailModalLabel">Event Details</h5>
        <button type="button"
                class="btn-close"
                data-bs-dismiss="modal"
                aria-label="Close"></button>
      </div>
      <div class="modal-body"
           id="eventDetailContent">
                  -->
        <!-- Event details will be populated here -->
        <!-- </div> -->
        <!--     <div class="modal-footer">
        <button type="button"
                class="btn btn-secondary"
                data-bs-dismiss="modal">Close</button>
        <button type="button"
                class="btn btn-primary"
                id="editEventBtn">Edit</button>
      </div>
    </div>
  </div>
</div>
-->