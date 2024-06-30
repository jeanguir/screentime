package com.example.screentime;

import java.io.BufferedReader;
import java.io.DataOutputStream;
import java.io.InputStreamReader;
import java.net.HttpURLConnection;
import java.net.URL;
import java.nio.charset.StandardCharsets;
import java.util.HashMap;
import java.util.Map;
import java.util.regex.Matcher;
import java.util.regex.Pattern;

public class Authorization {
    boolean isCorrect;

    boolean Auth(String login, String password) {
        try {
            String url = "http://monitor.incomecorp.ru/authorization.php";
            URL obj = new URL(url);
            HttpURLConnection con = (HttpURLConnection) obj.openConnection();

            // Set request method and parameters
            con.setRequestMethod("POST");
            con.setRequestProperty("Content-Type", "application/x-www-form-urlencoded");

            String urlParameters = "username=" + login + "&password=" + password;
            con.setDoOutput(true);
            try (DataOutputStream wr = new DataOutputStream(con.getOutputStream())) {
                wr.write(urlParameters.getBytes(StandardCharsets.UTF_8));
            }

            // Get response
            int responseCode = con.getResponseCode();
            //System.out.println("Response Code: " + responseCode);

            try (BufferedReader in = new BufferedReader(new InputStreamReader(con.getInputStream()))) {
                String inputLine;
                StringBuilder response = new StringBuilder();

                while ((inputLine = in.readLine()) != null) {
                    response.append(inputLine);
                }

                GetInstructionsSettings(response.toString());
            }
        } catch (Exception e) {
            e.printStackTrace();
        }

        return isCorrect;
    }

    void GetInstructionsSettings(String input) {

        Pattern pattern = Pattern.compile("\\[\"([^\"]+)\"\\]=>\\s*int\\((\\d+)\\)");
        Matcher matcher = pattern.matcher(input);

        // Создание карты для хранения переменных
        Map<String, Integer> variableMap = new HashMap<>();

        // Обработка найденных пар ключ-значение и добавление их в карту
        while (matcher.find()) {
            String key = matcher.group(1);
            int value = Integer.parseInt(matcher.group(2));
            variableMap.put(key, value);
        }

        try {
            int id = variableMap.get("id");
            int checkIntervalInSec = variableMap.get("check_interval_in_sec");
            int makeScreenshots = variableMap.get("make_screenshots");
            int trackWindows = variableMap.get("track_windows");
            int trackTabs = variableMap.get("track_tabs");
            int userId = variableMap.get("user_id");
            int inactivityPeriod = variableMap.get("inactivity_period_in_min");

            // Вывод значений переменных
            HelloApplication.id = userId;
            HelloApplication.screenshotInterval = checkIntervalInSec;
            HelloApplication.makeScreenshots = (makeScreenshots == 1);
            HelloApplication.countScreentime = (trackWindows == 1);
            HelloApplication.trackWindows = (trackWindows == 1);
            HelloApplication.trackTabs = (trackTabs == 1);
            HelloApplication.inactivityPeriod = inactivityPeriod;

            isCorrect = true;

        } catch (Exception exc) {
            System.out.println("Неверный логин или пароль");
            isCorrect = false;
        }
    }
}