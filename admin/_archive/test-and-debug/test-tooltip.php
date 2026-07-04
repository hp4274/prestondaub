<!DOCTYPE html>
<html>
<head>
    <title>Tooltip Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background: #f5f5f5;
        }
        .badge {
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 600;
            background: #E0E7FF;
            color: #3730A3;
            cursor: pointer;
            position: relative;
            display: inline-block;
            z-index: 100;
        }
        .interest-tooltip {
            position: fixed;
            background: #1F2937;
            color: white;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 500;
            white-space: pre-wrap;
            word-break: break-word;
            max-width: 300px;
            z-index: 9999;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3);
            line-height: 1.6;
            text-align: left;
            pointer-events: auto;
        }
        .interest-arrow {
            position: fixed;
            border: 6px solid transparent;
            border-top-color: #1F2937;
            z-index: 9999;
        }
    </style>
</head>
<body>
    <h1>Tooltip Test</h1>
    <p>Hover over the badge below:</p>
    
    <span class="badge interest-badge">
        5 Selected
        <div class="interest-tooltip" style="display: none;">Option A
Option B
Option C
Option D
Option E</div>
        <div class="interest-arrow" style="display: none;"></div>
    </span>

    <script>
        function attachTooltipToElement(badge) {
            const tooltip = badge.querySelector('.interest-tooltip');
            const arrow = badge.querySelector('.interest-arrow');
            
            console.log('Badge found:', badge);
            console.log('Tooltip found:', tooltip);
            console.log('Arrow found:', arrow);
            
            if (!tooltip || !arrow) {
                console.error('Tooltip or arrow not found');
                return;
            }
            
            function positionTooltip() {
                const badgeRect = badge.getBoundingClientRect();
                const tooltipWidth = 300;
                const tooltipHeight = tooltip.offsetHeight;
                const arrowHeight = 12;
                
                console.log('Badge rect:', badgeRect);
                console.log('Tooltip height:', tooltipHeight);
                
                // Position tooltip above the badge
                const tooltipLeft = badgeRect.left + (badgeRect.width / 2) - (tooltipWidth / 2);
                const tooltipTop = badgeRect.top - tooltipHeight - arrowHeight - 5;
                
                tooltip.style.left = tooltipLeft + 'px';
                tooltip.style.top = tooltipTop + 'px';
                
                // Position arrow below tooltip
                const arrowLeft = badgeRect.left + (badgeRect.width / 2) - 6;
                const arrowTop = badgeRect.top - arrowHeight - 5;
                
                arrow.style.left = arrowLeft + 'px';
                arrow.style.top = arrowTop + 'px';
                
                console.log('Tooltip positioned at:', tooltipLeft, tooltipTop);
                console.log('Arrow positioned at:', arrowLeft, arrowTop);
            }
            
            badge.addEventListener('mouseover', function(e) {
                console.log('Mouseover triggered');
                tooltip.style.display = 'block';
                arrow.style.display = 'block';
                positionTooltip();
            });
            
            badge.addEventListener('mouseout', function(e) {
                console.log('Mouseout triggered');
                tooltip.style.display = 'none';
                arrow.style.display = 'none';
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded');
            const badges = document.querySelectorAll('.interest-badge');
            console.log('Found badges:', badges.length);
            badges.forEach((badge, index) => {
                console.log('Processing badge', index);
                attachTooltipToElement(badge);
            });
        });
    </script>
</body>
</html>
