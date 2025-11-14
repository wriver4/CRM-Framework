<?php
/**
 * Content Form Helper
 * Renders the content editing form for a specific language
 */

function renderContentForm($templateId, $languageCode, $existingContent, $nonceToken) {
    $languageName = $languageCode === 'en' ? 'English' : 'Spanish';
    ?>
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><?php echo $languageName; ?> Content</h5>
        </div>
        <div class="card-body">
            <form action="post.php" method="POST">
                <input type="hidden" name="nonce" value="<?php echo $nonceToken; ?>">
                <input type="hidden" name="action" value="save_content">
                <input type="hidden" name="template_id" value="<?php echo $templateId; ?>">
                <input type="hidden" name="language_code" value="<?php echo $languageCode; ?>">
                
                <!-- Subject -->
                <div class="mb-3">
                    <label for="subject_<?php echo $languageCode; ?>" class="form-label">
                        Email Subject <span class="text-danger">*</span>
                    </label>
                    <input type="text" 
                           class="form-control" 
                           id="subject_<?php echo $languageCode; ?>" 
                           name="subject" 
                           value="<?php echo htmlspecialchars($existingContent['subject'] ?? ''); ?>"
                           placeholder="Enter email subject line"
                           required>
                    <div class="form-text">You can use variables like {{lead_name}} in the subject.</div>
                </div>
                
                <!-- HTML Body -->
                <div class="mb-3">
                    <label for="body_html_<?php echo $languageCode; ?>" class="form-label">
                        HTML Body <span class="text-danger">*</span>
                    </label>
                    <textarea class="form-control font-monospace" 
                              id="body_html_<?php echo $languageCode; ?>" 
                              name="body_html" 
                              rows="15"
                              required><?php echo htmlspecialchars($existingContent['body_html'] ?? ''); ?></textarea>
                    <div class="form-text">
                        HTML content for the email. Use {{variable_key}} for variable substitution.
                    </div>
                </div>
                
                <!-- Plain Text Body -->
                <div class="mb-3">
                    <label for="body_plain_text_<?php echo $languageCode; ?>" class="form-label">
                        Plain Text Body <small class="text-muted">(Optional)</small>
                    </label>
                    <textarea class="form-control font-monospace" 
                              id="body_plain_text_<?php echo $languageCode; ?>" 
                              name="body_plain_text" 
                              rows="10"><?php echo htmlspecialchars($existingContent['body_plain_text'] ?? ''); ?></textarea>
                    <div class="form-text">
                        Plain text version for email clients that don't support HTML.
                    </div>
                </div>
                
                <div class="d-flex justify-content-between">
                    <a href="view.php?id=<?php echo $templateId; ?>" class="btn btn-secondary">
                        <i class="fa fa-arrow-left"></i> Back to Template
                    </a>
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-save"></i> Save <?php echo $languageName; ?> Content
                    </button>
                </div>
            </form>
            
            <?php if ($existingContent): ?>
            <div class="alert alert-success mt-3">
                <i class="fa fa-check-circle"></i> <?php echo $languageName; ?> content exists.
                Last updated: <?php echo date('M d, Y g:i A', strtotime($existingContent['updated_at'])); ?>
            </div>
            <?php else: ?>
            <div class="alert alert-warning mt-3">
                <i class="fa fa-exclamation-triangle"></i> No <?php echo $languageName; ?> content yet. Please add content above.
            </div>
            <?php endif; ?>
            
            <!-- HTML Template Example -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fa fa-code"></i> HTML Template Example</h6>
                </div>
                <div class="card-body">
                    <pre class="mb-0"><code>&lt;!DOCTYPE html&gt;
&lt;html&gt;
&lt;head&gt;
    &lt;meta charset="UTF-8"&gt;
    &lt;title&gt;{{subject}}&lt;/title&gt;
&lt;/head&gt;
&lt;body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;"&gt;
    &lt;div style="max-width: 600px; margin: 0 auto; padding: 20px;"&gt;
        &lt;h2&gt;Hello {{lead_name}},&lt;/h2&gt;
        
        &lt;p&gt;Thank you for your interest in {{service_name}}.&lt;/p&gt;
        
        &lt;p&gt;Your assigned representative is &lt;strong&gt;{{assigned_user}}&lt;/strong&gt;.&lt;/p&gt;
        
        &lt;p&gt;If you have any questions, please contact us at:&lt;/p&gt;
        &lt;ul&gt;
            &lt;li&gt;Phone: {{company_phone}}&lt;/li&gt;
            &lt;li&gt;Email: {{company_email}}&lt;/li&gt;
        &lt;/ul&gt;
        
        &lt;p&gt;Best regards,&lt;br&gt;
        {{company_name}}&lt;/p&gt;
    &lt;/div&gt;
&lt;/body&gt;
&lt;/html&gt;</code></pre>
                </div>
            </div>
        </div>
    </div>
    <?php
}