package com.example.screentime;

import com.google.gson.Gson;

import java.io.IOException;
import java.io.OutputStream;
import java.net.HttpURLConnection;
import java.net.URL;
import java.nio.charset.StandardCharsets;
import java.time.LocalDateTime;
import java.time.format.DateTimeFormatter;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

class SendScreentime extends Thread {

    Map<String, Double> window;
    Map<String, String> windowStart;
    Map<String, String> windowEnd;

    Map<String, Float> inactivity = new HashMap<>();
    Map<String, String> inactivityStartEnd = new HashMap<>();

    public void run() {
        while(HelloApplication.countScreentime) {
            try {
                Thread.sleep(10000);
                if (!ScreentimeTracker.goneAway) {
                    SendActivity();
                }
            } catch (InterruptedException e) {
                throw new RuntimeException(e);
            }
        }

        System.out.println("Поток SendScreentime завершен");
    }

    void SendActivity() {
        window = HelloController.window;
        windowStart = HelloController.windowStart;
        windowEnd = HelloController.windowEnd;

        // Создание JSON объекта
        Map<String, Object> jsonMap = new HashMap<>();
        jsonMap.put("user_id", HelloApplication.id);

        // Преобразование данных из HashMap в список
        List<Map<String, Object>> hashMapsList = new ArrayList<>();
        for (Map.Entry<String, Double> entry : window.entrySet()) {
            Map<String, Object> entryMap = new HashMap<>();
            entryMap.put("name", entry.getKey());
            entryMap.put("time", entry.getValue());
            entryMap.put("start", windowStart.get(entry.getKey())); // Добавление значения windowStart
            entryMap.put("end", windowEnd.get(entry.getKey())); // Добавление значения windowEnd
            hashMapsList.add(entryMap);
        }

        jsonMap.put("hashMaps", hashMapsList);

        String hours = String.valueOf(ScreentimeTracker.ActivityTimeInMin / 60f);
        jsonMap.put("hours", hours.substring(0, 4));

        // Преобразование в JSON строку
        Gson gson = new Gson();
        String jsonString = gson.toJson(jsonMap);

        System.out.println(jsonString);

        try {
            URL url = new URL("http://monitor.incomecorp.ru/post_windows.php");
            HttpURLConnection connection = (HttpURLConnection) url.openConnection();

            connection.setRequestMethod("POST");
            connection.setDoOutput(true);

            try (OutputStream os = connection.getOutputStream()) {
                // Запись JSON-строки в поток
                byte[] input = jsonString.getBytes(StandardCharsets.UTF_8);
                os.write(input, 0, input.length);
            }

            int responseCode = connection.getResponseCode();
            //System.out.println("\n\n\nRESP CODE: " + responseCode + "\n\n\n");

            connection.disconnect();
        }
        catch (IOException e) {
            throw new RuntimeException(e);
        }
    }

    void SendInactivity() {
        inactivity = HelloController.inactivity;
        inactivityStartEnd = HelloController.inactivityStartEnd;

        // Создание JSON объекта
        Map<String, Object> jsonMap = new HashMap<>();
        jsonMap.put("user_id", HelloApplication.id);

        // Преобразование данных из HashMap в список
        List<Map<String, Object>> hashMapsList = new ArrayList<>();
        for (Map.Entry<String, Float> entry : inactivity.entrySet()) {
            Map<String, Object> entryMap = new HashMap<>();
            entryMap.put("start", entry.getKey());
            entryMap.put("duration", entry.getValue());
            entryMap.put("end", inactivityStartEnd.get(entry.getKey())); // Добавление значения windowEnd
            hashMapsList.add(entryMap);
        }

        jsonMap.put("hashMaps", hashMapsList);

        // Преобразование в JSON строку
        Gson gson = new Gson();
        String jsonString = gson.toJson(jsonMap);

        System.out.println(jsonString);

        try {
            URL url = new URL("http://monitor.incomecorp.ru/post_inactivity.php");
            HttpURLConnection connection = (HttpURLConnection) url.openConnection();

            connection.setRequestMethod("POST");
            connection.setDoOutput(true);

            try (OutputStream os = connection.getOutputStream()) {
                byte[] input = jsonString.getBytes(StandardCharsets.UTF_8);
                os.write(input, 0, input.length);
            }

            int responseCode = connection.getResponseCode();
            //System.out.println("\n\n\nRESP CODE: " + responseCode + "\n\n\n");

            connection.disconnect();
        }
        catch (IOException e) {
            System.out.println("Потеряно соединение с интернетом");
        }
    }
}