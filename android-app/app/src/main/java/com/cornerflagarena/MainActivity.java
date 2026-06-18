package com.cornerflagarena;

import android.content.Context;
import android.graphics.Bitmap;
import android.net.ConnectivityManager;
import android.net.NetworkInfo;
import android.os.Bundle;
import android.os.Handler;
import android.os.Looper;
import android.view.View;
import android.view.animation.AlphaAnimation;
import android.webkit.WebChromeClient;
import android.webkit.WebSettings;
import android.webkit.WebView;
import android.webkit.WebViewClient;
import android.widget.Button;
import android.widget.ProgressBar;
import android.widget.RelativeLayout;
import android.widget.LinearLayout;
import android.widget.Toast;

import androidx.appcompat.app.AppCompatActivity;

public class MainActivity extends AppCompatActivity {

    // Default WebView URL: points to local XAMPP server on host machine from Android Emulator.
    // Replace this with your hosted domain URL when deploying online.
    private static final String APP_URL = "http://10.0.2.2/corner-flag-arena/";
    private static final int SPLASH_DELAY_MS = 2500;

    private WebView webView;
    private ProgressBar loadingProgress;
    private RelativeLayout splashLayout;
    private LinearLayout offlineLayout;
    private Button btnRetry;
    private boolean isLoaded = false;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);

        // Bind Views
        webView = findViewById(R.id.webView);
        loadingProgress = findViewById(R.id.loadingProgressBar);
        splashLayout = findViewById(R.id.splashLayout);
        offlineLayout = findViewById(R.id.offlineLayout);
        btnRetry = findViewById(R.id.btnRetry);

        // Set up WebView Settings
        WebSettings webSettings = webView.getSettings();
        webSettings.setJavaScriptEnabled(true);
        webSettings.setDomStorageEnabled(true);
        webSettings.setDatabaseEnabled(true);
        webSettings.setLoadWithOverviewMode(true);
        webSettings.setUseWideViewPort(true);
        webSettings.setSupportZoom(false);
        webSettings.setBuiltInZoomControls(false);
        webSettings.setCacheMode(WebSettings.LOAD_DEFAULT);

        // Enable Clear Cache and Cookies in Web View (optional)
        webView.setScrollBarStyle(View.SCROLLBARS_INSIDE_OVERLAY);

        // Setup clients
        webView.setWebViewClient(new WebViewClient() {
            @Override
            public void onPageStarted(WebView view, String url, Bitmap favicon) {
                super.onPageStarted(view, url, favicon);
                if (isOnline()) {
                    loadingProgress.setVisibility(View.VISIBLE);
                }
            }

            @Override
            public void onPageFinished(WebView view, String url) {
                super.onPageFinished(view, url);
                loadingProgress.setVisibility(View.GONE);
                isLoaded = true;
            }

            @Override
            public void onReceivedError(WebView view, int errorCode, String description, String failingUrl) {
                // If local loopback or server fails to connect, display offline layout
                showOfflineView();
            }
        });

        webView.setWebChromeClient(new WebChromeClient() {
            @Override
            public void onProgressChanged(WebView view, int newProgress) {
                super.onProgressChanged(view, newProgress);
                // Can tie progress bar level here if needed
            }
        });

        // Retry button click handler
        btnRetry.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                tryLoadUrl();
            }
        });

        // Initialize Web loading
        tryLoadUrl();

        // Splash screen fadeout transition handler
        new Handler(Looper.getMainLooper()).postDelayed(new Runnable() {
            @Override
            public void run() {
                fadeOutSplashScreen();
            }
        }, SPLASH_DELAY_MS);

        // Optional Notification Structure ready check (e.g. init channels)
        initNotificationPlaceholder();
    }

    private void tryLoadUrl() {
        if (isOnline()) {
            offlineLayout.setVisibility(View.GONE);
            webView.setVisibility(View.VISIBLE);
            webView.loadUrl(APP_URL);
        } else {
            showOfflineView();
        }
    }

    private void showOfflineView() {
        webView.setVisibility(View.GONE);
        loadingProgress.setVisibility(View.GONE);
        offlineLayout.setVisibility(View.VISIBLE);
        Toast.makeText(this, "Network Connection Failed", Toast.LENGTH_SHORT).show();
    }

    private void fadeOutSplashScreen() {
        if (splashLayout.getVisibility() == View.VISIBLE) {
            AlphaAnimation fade = new AlphaAnimation(1.0f, 0.0f);
            fade.setDuration(400);
            splashLayout.startAnimation(fade);
            splashLayout.setVisibility(View.GONE);
        }
    }

    private boolean isOnline() {
        ConnectivityManager cm = (ConnectivityManager) getSystemService(Context.CONNECTIVITY_SERVICE);
        if (cm != null) {
            NetworkInfo activeNetwork = cm.getActiveNetworkInfo();
            return activeNetwork != null && activeNetwork.isConnectedOrConnecting();
        }
        return false;
    }

    @Override
    public void onBackPressed() {
        // If web history is back-navigable, route back. Otherwise close app.
        if (webView.getVisibility() == View.VISIBLE && webView.canGoBack()) {
            webView.goBack();
        } else {
            super.onBackPressed();
        }
    }

    private void initNotificationPlaceholder() {
        // Prepare notification channel registry for future push notifications.
        // Ready to integrate with FCM (Firebase Cloud Messaging).
        if (android.os.Build.VERSION.SDK_INT >= android.os.Build.VERSION_CODES.O) {
            String channelId = "booking_updates";
            CharSequence name = "Booking Status Alerts";
            String description = "Get alerts when your booking gets approved or rejected.";
            int importance = android.app.NotificationManager.IMPORTANCE_DEFAULT;
            android.app.NotificationChannel channel = new android.app.NotificationChannel(channelId, name, importance);
            channel.setDescription(description);
            android.app.NotificationManager notificationManager = getSystemService(android.app.NotificationManager.class);
            if (notificationManager != null) {
                notificationManager.createNotificationChannel(channel);
            }
        }
    }
}
