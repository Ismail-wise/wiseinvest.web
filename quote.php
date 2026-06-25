<?php 
include 'includes/db.php'; 
include 'includes/header.php'; 

// Default to Apple if no symbol provided
$symbol = isset($_GET['symbol']) ? htmlspecialchars(strtoupper($_GET['symbol'])) : 'AAPL';
?>

<div style="background:#0f172a; padding: 40px 0; position: relative; z-index: 1;">
    <div style="max-width: 1400px; margin: 0 auto; padding: 0 20px;">
        
        <div class="tradingview-widget-container" style="margin-bottom: 20px;">
            <div class="tradingview-widget-container__widget"></div>
            <script type="text/javascript" src="https://s3.tradingview.com/external-embedding/embed-widget-symbol-info.js" async>
            {
            "symbol": "NASDAQ:<?php echo $symbol; ?>",
            "width": "100%",
            "locale": "en",
            "colorTheme": "dark",
            "isTransparent": false
            }
            </script>
        </div>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
            
            <div style="background: #1e293b; border-radius: 12px; overflow: hidden; height: 600px;">
                <div class="tradingview-widget-container" style="height:100%; width:100%">
                    <div class="tradingview-widget-container__widget" style="height:calc(100% - 32px); width:100%"></div>
                    <script type="text/javascript" src="https://s3.tradingview.com/external-embedding/embed-widget-advanced-chart.js" async>
                    {
                    "autosize": true,
                    "symbol": "NASDAQ:<?php echo $symbol; ?>",
                    "interval": "D",
                    "timezone": "Etc/UTC",
                    "theme": "dark",
                    "style": "1",
                    "locale": "en",
                    "enable_publishing": false,
                    "backgroundColor": "rgba(30, 41, 59, 1)",
                    "hide_top_toolbar": false,
                    "save_image": false,
                    "calendar": false,
                    "hide_volume": true,
                    "support_host": "https://www.tradingview.com"
                    }
                    </script>
                </div>
            </div>

            <div style="display: flex; flex-direction: column; gap: 20px;">
                
                <div class="tradingview-widget-container">
                    <div class="tradingview-widget-container__widget"></div>
                    <script type="text/javascript" src="https://s3.tradingview.com/external-embedding/embed-widget-symbol-profile.js" async>
                    {
                    "width": "100%",
                    "height": "350",
                    "colorTheme": "dark",
                    "isTransparent": false,
                    "symbol": "NASDAQ:<?php echo $symbol; ?>",
                    "locale": "en"
                    }
                    </script>
                </div>
                
                <div class="tradingview-widget-container">
                    <div class="tradingview-widget-container__widget"></div>
                    <script type="text/javascript" src="https://s3.tradingview.com/external-embedding/embed-widget-financials.js" async>
                    {
                    "colorTheme": "dark",
                    "isTransparent": false,
                    "largeChartUrl": "",
                    "displayMode": "regular",
                    "width": "100%",
                    "height": "230",
                    "symbol": "NASDAQ:<?php echo $symbol; ?>",
                    "locale": "en"
                    }
                    </script>
                </div>
            </div>

        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>