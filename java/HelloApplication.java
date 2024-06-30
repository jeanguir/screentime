package com.example.screentime;

import javafx.application.Application;
import javafx.application.Platform;
import javafx.fxml.FXMLLoader;
import javafx.scene.Scene;

import javafx.stage.Stage;
import java.awt.*;
import java.io.IOException;
import java.net.HttpURLConnection;
import java.net.URL;

public class HelloApplication extends Application {

    public static Screenshots scs;
    public static SendScreentime ss;
    public static ScreentimeTracker st;

    public static int screenshotInterval;
    public static boolean makeScreenshots;
    public static boolean countScreentime;
    public static boolean trackWindows;
    public static boolean trackTabs;
    public static int id = 0;
    public static int inactivityPeriod;

    private static TrayIcon trayIcon;
    private static SystemTray systemTray;

    private static Stage stage;


    public static void main(String[] args) {

        Runtime.getRuntime().addShutdownHook(new Thread(() -> {
            System.out.println("Выполняется код перед закрытием программы...");
            switchStatusTo("offline");

        }));

        launch();
    }

    @Override
    public void start(Stage primaryStage) throws IOException {
        this.stage = primaryStage;

        FXMLLoader fxmlLoader = new FXMLLoader(getClass().getResource("hello-view.fxml"));
        Scene scene = new Scene(fxmlLoader.load(), 320, 240);
        primaryStage.setTitle("Отслеживание активности");
        primaryStage.setScene(scene);
        primaryStage.show();

        // Добавляем обработчик закрытия окна
        primaryStage.setOnCloseRequest(event -> {
            // Предотвращаем закрытие окна по умолчанию
            event.consume();
            // Сворачиваем в трей вместо закрытия
            minimizeToTray(primaryStage);
        });
    }

    public static void minimizeToTray(Stage stage) {
        if (!SystemTray.isSupported()) {
            System.out.println("SystemTray is not supported");
            return;
        }

        systemTray = SystemTray.getSystemTray();

        Image image = Toolkit.getDefaultToolkit().getImage("icon.png");
        PopupMenu popup = new PopupMenu();

        MenuItem exitItem = new MenuItem("Exit");
        exitItem.addActionListener(e -> {
            System.exit(0);
        });

        popup.add(exitItem);

        trayIcon = new TrayIcon(image, "Your Application", popup);
        trayIcon.setImageAutoSize(true);

        try {
            systemTray.add(trayIcon);
            stage.hide();
        } catch (AWTException e) {
            e.printStackTrace();
        }
    }

    static void callMinimizeToTray() {
         minimizeToTray(stage);
    }


    static void initTracker() {
        scs = new Screenshots(makeScreenshots, screenshotInterval);
        scs.start();

        ss = new SendScreentime();
        ss.start();

        st = new ScreentimeTracker(countScreentime, screenshotInterval);
    }

    static void switchStatusTo(String status) {
        try {
            String url = null;
            if (status == "offline")
                url = "http://monitor.incomecorp.ru/switch_status.php?user_id=" + id;
            else
                url = "http://monitor.incomecorp.ru/switch_status_on.php?user_id=" + id;


            URL obj = new URL(url);
            HttpURLConnection con = (HttpURLConnection) obj.openConnection();

            // Set request method
            con.setRequestMethod("GET");

            // Optional request header
            con.setRequestProperty("Content-Type", "application/x-www-form-urlencoded");

            // Send GET request
            int responseCode = con.getResponseCode();
            //System.out.println("GET Response Code :: " + responseCode);

            // Read response
            if (responseCode == HttpURLConnection.HTTP_OK) {
                // Success
                System.out.println("Status change successful!");
            } else {
                // Error
                System.out.println("GET request failed");
            }

        } catch (Exception e) {
            e.printStackTrace();
        }
    }
}