<?php
require 'config.php';

if (!isset($_GET['user_id'])) {
    exit("No user ID provided");
}

$user_id = $_GET['user_id'];
$stmt = $pdo->prepare("SELECT * FROM verification_requests WHERE user_id = ?");
$stmt->execute([$user_id]);
$request = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$request) {
    echo "No verification request found.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verification Request Details</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            margin: 0;
            padding: 20px;
            color: #2c3e50;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            margin: 0;
            font-size: 1.8rem;
            font-weight: 600;
        }
        
        .content {
            padding: 30px;
        }
        
        .info-section {
            margin-bottom: 30px;
        }
        
        .info-row {
            display: flex;
            margin-bottom: 15px;
            padding: 12px 0;
            border-bottom: 1px solid #ecf0f1;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 600;
            color: #495057;
            min-width: 150px;
            flex-shrink: 0;
        }
        
        .info-value {
            color: #2c3e50;
            flex: 1;
        }
        
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-approved {
            background: #d4edda;
            color: #155724;
        }
        
        .status-rejected {
            background: #f8d7da;
            color: #721c24;
        }
        
        .rejection-reason {
            background: #fff3cd;
            color: #856404;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #ffc107;
            margin-top: 10px;
            font-style: italic;
        }
        
        .attachments-section {
            margin-top: 30px;
        }
        
        .section-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
        }
        
        .attachment-group {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid #e9ecef;
        }
        
        .attachment-header {
            font-weight: 600;
            color: #495057;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .attachment-icon {
            font-size: 1.2rem;
        }
        
        .id-number {
            background: #e3f2fd;
            color: #1565c0;
            padding: 8px 12px;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-weight: 600;
            letter-spacing: 1px;
            margin-bottom: 15px;
            display: inline-block;
        }
        
        .attachment-preview {
            display: inline-block;
            margin: 5px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transition: transform 0.3s ease;
        }
        
        .attachment-preview:hover {
            transform: scale(1.05);
        }
        
        .attachment-preview img {
            max-width: 150px;
            max-height: 150px;
            object-fit: cover;
            border: none;
            display: block;
        }
        
        .pdf-preview {
            background: #dc3545;
            color: white;
            padding: 20px;
            text-align: center;
            min-width: 150px;
            min-height: 150px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-decoration: none;
        }
        
        .pdf-preview:hover {
            background: #c82333;
            color: white;
            text-decoration: none;
        }
        
        .pdf-icon {
            font-size: 3rem;
            margin-bottom: 10px;
        }
        
        .no-attachment {
            color: #6c757d;
            font-style: italic;
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            border-radius: 8px;
            border: 2px dashed #dee2e6;
        }
        
        .metadata {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-top: 30px;
        }
        
        .metadata-title {
            font-weight: 600;
            color: #495057;
            margin-bottom: 15px;
        }
        
        .metadata-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .metadata-label {
            color: #6c757d;
        }
        
        .metadata-value {
            color: #495057;
            font-weight: 500;
        }
        
        @media (max-width: 768px) {
            .container {
                margin: 10px;
                border-radius: 10px;
            }
            
            .content {
                padding: 20px;
            }
            
            .info-row {
                flex-direction: column;
                gap: 5px;
            }
            
            .info-label {
                min-width: auto;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📋 Verification Request Details</h1>
        </div>
        
        <div class="content">
            <div class="info-section">
                <div class="info-row">
                    <div class="info-label">👤 Full Name:</div>
                    <div class="info-value"><?php echo htmlspecialchars($request['full_name']); ?></div>
                </div>
                
                <div class="info-row">
                    <div class="info-label">📅 Birthdate:</div>
                    <div class="info-value"><?php echo date('F j, Y', strtotime($request['birthdate'])); ?></div>
                </div>
                
                <div class="info-row">
                    <div class="info-label">📊 Status:</div>
                    <div class="info-value">
                        <span class="status-badge status-<?php echo $request['status']; ?>">
                            <?php 
                            echo match($request['status']) {
                                'pending' => '⏳ Pending Review',
                                'approved' => '✅ Approved',
                                'rejected' => '❌ Rejected',
                                default => ucfirst($request['status'])
                            };
                            ?>
                        </span>
                    </div>
                </div>
                
                <?php if (!empty($request['rejection_reason'])): ?>
                <div class="info-row">
                    <div class="info-label">📝 Rejection Reason:</div>
                    <div class="info-value">
                        <div class="rejection-reason">
                            <?php echo htmlspecialchars($request['rejection_reason']); ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="attachments-section">
                <h2 class="section-title">📎 Submitted Documents</h2>
                
                <?php
                $attachmentTitles = [
                    1 => ['title' => 'Primary ID Document', 'icon' => '🆔', 'required' => true],
                    2 => ['title' => 'Secondary ID Document', 'icon' => '🆔', 'required' => false],
                    3 => ['title' => 'Additional Document', 'icon' => '📄', 'required' => false]
                ];
                
                for ($i = 1; $i <= 3; $i++):
                    $attachmentField = "attachment_" . $i;
                    $idNumberField = "id_number_" . $i;
                    $hasAttachment = !empty($request[$attachmentField]);
                    $hasIdNumber = !empty($request[$idNumberField]);
                    $config = $attachmentTitles[$i];
                ?>
                    <div class="attachment-group">
                        <div class="attachment-header">
                            <span class="attachment-icon"><?php echo $config['icon']; ?></span>
                            <span><?php echo $config['title']; ?></span>
                            <?php if ($config['required']): ?>
                                <span style="color: #e74c3c; font-size: 12px;">(Required)</span>
                            <?php else: ?>
                                <span style="color: #6c757d; font-size: 12px;">(Optional)</span>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($hasIdNumber): ?>
                            <div style="margin-bottom: 15px;">
                                <strong>ID Number:</strong>
                                <div class="id-number"><?php echo htmlspecialchars($request[$idNumberField]); ?></div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($hasAttachment): ?>
                            <div>
                                <strong>Uploaded File:</strong><br>
                                <?php 
                                $filePath = $request[$attachmentField];
                                $fileExtension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                                $fileName = basename($filePath);
                                ?>
                                
                                <?php if (in_array($fileExtension, ['jpg', 'jpeg', 'png'])): ?>
                                    <a href="<?php echo htmlspecialchars($filePath); ?>" target="_blank" class="attachment-preview">
                                        <img src="<?php echo htmlspecialchars($filePath); ?>" 
                                             alt="<?php echo $config['title']; ?>"
                                             title="Click to view full size">
                                    </a>
                                <?php elseif ($fileExtension === 'pdf'): ?>
                                    <a href="<?php echo htmlspecialchars($filePath); ?>" target="_blank" class="attachment-preview">
                                        <div class="pdf-preview">
                                            <div class="pdf-icon">📄</div>
                                            <div style="font-size: 12px; font-weight: 600;">PDF Document</div>
                                            <div style="font-size: 10px; opacity: 0.8;">Click to view</div>
                                        </div>
                                    </a>
                                <?php endif; ?>
                                
                                <div style="margin-top: 10px; font-size: 12px; color: #6c757d;">
                                    <strong>File:</strong> <?php echo htmlspecialchars($fileName); ?><br>
                                    <?php if (file_exists($filePath)): ?>
                                        <strong>Size:</strong> <?php echo number_format(filesize($filePath) / 1024 / 1024, 2); ?> MB
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="no-attachment">
                                <?php if ($config['required']): ?>
                                    ⚠️ Required document not uploaded
                                <?php else: ?>
                                    📭 No document uploaded
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!$hasAttachment && !$hasIdNumber && !$config['required']): ?>
                            <div style="color: #6c757d; font-size: 14px; text-align: center; padding: 10px;">
                                This optional document was not provided.
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endfor; ?>
            </div>
            
            <div class="metadata">
                <div class="metadata-title">📊 Request Metadata</div>
                <div class="metadata-row">
                    <span class="metadata-label">Request ID:</span>
                    <span class="metadata-value">#<?php echo $request['id']; ?></span>
                </div>
                <div class="metadata-row">
                    <span class="metadata-label">User ID:</span>
                    <span class="metadata-value"><?php echo $request['user_id']; ?></span>
                </div>
                <div class="metadata-row">
                    <span class="metadata-label">Submitted:</span>
                    <span class="metadata-value"><?php echo date('F j, Y \a\t g:i A', strtotime($request['created_at'])); ?></span>
                </div>
                <?php if ($request['updated_at'] != $request['created_at']): ?>
                <div class="metadata-row">
                    <span class="metadata-label">Last Updated:</span>
                    <span class="metadata-value"><?php echo date('F j, Y \a\t g:i A', strtotime($request['updated_at'])); ?></span>
                </div>
                <?php endif; ?>
                <?php if (!empty($request['processed_by'])): ?>
                <div class="metadata-row">
                    <span class="metadata-label">Processed By:</span>
                    <span class="metadata-value">Admin #<?php echo $request['processed_by']; ?></span>
                </div>
                <?php endif; ?>
                <?php if (!empty($request['processed_at'])): ?>
                <div class="metadata-row">
                    <span class="metadata-label">Processed At:</span>
                    <span class="metadata-value"><?php echo date('F j, Y \a\t g:i A', strtotime($request['processed_at'])); ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        // Add lightbox functionality for images
        document.querySelectorAll('.attachment-preview img').forEach(img => {
            img.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Create lightbox overlay
                const overlay = document.createElement('div');
                overlay.style.cssText = `
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0,0,0,0.8);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    z-index: 9999;
                    cursor: pointer;
                `;
                
                // Create enlarged image
                const enlargedImg = document.createElement('img');
                enlargedImg.src = this.src;
                enlargedImg.style.cssText = `
                    max-width: 90%;
                    max-height: 90%;
                    border-radius: 10px;
                    box-shadow: 0 10px 30px rgba(0,0,0,0.5);
                `;
                
                overlay.appendChild(enlargedImg);
                document.body.appendChild(overlay);
                
                // Close on click
                overlay.addEventListener('click', () => {
                    document.body.removeChild(overlay);
                });
                
                // Close on escape key
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') {
                        if (document.body.contains(overlay)) {
                            document.body.removeChild(overlay);
                        }
                    }
                });
            });
        });
    </script>
</body>
</html>