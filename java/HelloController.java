package com.example.screentime;

import javafx.fxml.FXML;

import javafx.scene.control.Button;
import javafx.scene.control.Label;
import javafx.scene.control.PasswordField;
import javafx.scene.control.TextField;

import java.util.HashMap;
import java.util.Map;


public class HelloController {

    @FXML
    public Button loginButton;
    @FXML
    private TextField usernameField;

    @FXML
    private PasswordField passwordField;

    @FXML
    Label welcomeText;

    public static Map<String, Double> window;
    public static Map <String, String> windowStart;
    public static Map<String, String> windowEnd;

    public static Map<String, Float> inactivity = new HashMap<>();
    public static Map<String, String> inactivityStartEnd = new HashMap<>();

    public boolean isAuthorized;
    public boolean correct;




    @FXML
    protected void onLoginButtonClick() {
        String login = usernameField.getText();
        String password = passwordField.getText();

        Screenshots scs = HelloApplication.scs;
        SendScreentime ss = HelloApplication.ss;
        ScreentimeTracker st = HelloApplication.st;

        if(!isAuthorized) {
            GetInstructions(login, password);
            if (correct) {
                welcomeText.setText("Вы успешно вошли! Для выхода введите \nпароль администратора");
                ((Button) usernameField.getScene().lookup("#loginButton")).setText("Выйти");
                isAuthorized = true;
                HelloApplication.initTracker();
                HelloApplication.callMinimizeToTray();
            } else {
                welcomeText.setText("Неправильный логин или пароль");
            }
        } else {
            scs.ms = false;
            st.countScreentime = false;
            HelloApplication.countScreentime = false;
            isAuthorized = false;
        }
    }

    void GetInstructions(String login, String password) {
        Authorization auth = new Authorization();
        correct = auth.Auth(login, password);
    }
}