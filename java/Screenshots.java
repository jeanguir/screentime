package com.example.screentime;

import org.apache.http.HttpResponse;
import org.apache.http.client.HttpClient;
import org.apache.http.client.methods.HttpPost;
import org.apache.http.entity.mime.MultipartEntityBuilder;
import org.apache.http.impl.client.HttpClients;

import javax.imageio.ImageIO;
import java.awt.*;
import java.awt.image.BufferedImage;
import java.io.BufferedReader;
import java.io.ByteArrayOutputStream;
import java.io.IOException;
import java.io.InputStreamReader;
import java.time.LocalDateTime;
import java.time.format.DateTimeFormatter;

@SuppressWarnings("BusyWait")
public class Screenshots extends Thread {
    public boolean ms;
    int interval;


    Screenshots(boolean makeScreenshots, int screenshotsInterval) {
        ms = makeScreenshots;
        interval = screenshotsInterval * 1000;
    }

    public void run() {
        while (ms) {
            try {
                Robot robot = new Robot();
                Rectangle screenRect = new Rectangle(Toolkit.getDefaultToolkit().getScreenSize());
                BufferedImage screenshot = robot.createScreenCapture(screenRect);

                // Преобразование BufferedImage в массив байтов
                ByteArrayOutputStream byteArrayOutputStream = new ByteArrayOutputStream();
                ImageIO.write(screenshot, "png", byteArrayOutputStream);
                byte[] imageData = byteArrayOutputStream.toByteArray();

                // Добавление переменной int
                int user_id = HelloApplication.id;

                // Отправка POST-запроса на PHP скрипт
                sendPostRequest(imageData, user_id);

            } catch (AWTException | IOException e) {
                System.out.println("Ошибка: " + e.getMessage());
            }

            try {
                Thread.sleep(interval);
            } catch (InterruptedException e) {
                throw new RuntimeException(e);
            }
        }

        System.out.println("Поток Screenshots завершен");
    }

    private void sendPostRequest(byte[] imageData, int myVariable) {
        try {
            String phpScriptUrl = "http://monitor.incomecorp.ru/post_screenshots.php"; // Замените на реальный URL вашего PHP скрипта
            HttpClient httpClient = HttpClients.createDefault();
            HttpPost httpPost = new HttpPost(phpScriptUrl);

            // Добавление скриншота и других параметров в POST-запрос
            MultipartEntityBuilder builder = MultipartEntityBuilder.create();
            builder.addBinaryBody("screenshot", imageData, org.apache.http.entity.ContentType.IMAGE_PNG, "screenshot.png");
            builder.addTextBody("user_id", String.valueOf(myVariable));

            httpPost.setEntity(builder.build());

            // Выполнение POST-запроса
            HttpResponse response = httpClient.execute(httpPost);

            BufferedReader reader = new BufferedReader(new InputStreamReader(response.getEntity().getContent()));
            String line;
            while ((line = reader.readLine()) != null) {
                System.out.println(line);
            }

        } catch (IOException e) {
            // Обработка ошибки отправки POST-запроса
        }
    }
}