package com.example.screentime;

import com.sun.jna.Native;
import com.sun.jna.platform.win32.User32;
import com.sun.jna.platform.win32.WinDef;

import java.awt.*;
import java.time.LocalDateTime;
import java.time.format.DateTimeFormatter;
import java.util.HashMap;
import java.util.Map;

public class ScreentimeTracker extends Thread {

    public final Map<String, Double> window = new HashMap<>();
    public final Map<String, String> windowStart = new HashMap<>();
    public final Map<String, String> windowEnd = new HashMap<>();

    public final Map<String, Float> inactivity = new HashMap<>();
    public final Map<String, String> inactivityStartEnd = new HashMap<>();

    boolean countScreentime;
    static long screenshotInterval;

    int previousX, previousY = 0;

    float inactivityInMin = 0;

    public static float ActivityTimeInMin = 1f;

    public static boolean goneAway;

    String inactivityStart;
    private boolean statusAlreadySwitched;

    ScreentimeTracker(boolean cs, long ci) {
        countScreentime = cs;
        screenshotInterval = ci;
        start();
    }

    @SuppressWarnings("BusyWait")
    public void run() {
        try
        {
            while (countScreentime) {
                ActiveSessionCounting();
                HelloController.window = window;
                HelloController.windowStart = windowStart;
                HelloController.windowEnd = windowEnd;

                HelloController.inactivity = inactivity;
                HelloController.inactivityStartEnd = inactivityStartEnd;

                Thread.sleep(2000);
            }
        } catch (InterruptedException ie) {
            System.out.println("InterruptedException has been caught");
        }

        System.out.println("Поток ScreentimeTracker завершен");
    }

    @SuppressWarnings("UnusedAssignment")
    void ActiveSessionCounting() {
        String activeWindowTitle = getActiveWindowTitle();

        if(window.containsKey(activeWindowTitle) && !goneAway) {
            windowEnd.put(activeWindowTitle, getTime("HH:mm"));
            double activeSession = window.get(activeWindowTitle) + 0.03;
            window.put(activeWindowTitle, activeSession);
        }
        else if (!window.containsKey(activeWindowTitle)) {
            windowStart.put(activeWindowTitle, getTime("HH:mm"));
            window.put(activeWindowTitle, 0.03);
        }

        System.out.println(activeWindowTitle + " " + window.get(activeWindowTitle) + " minutes");

        // ПРОВЕРКА АКТИВНОСТИ
        PointerInfo pointerInfo = MouseInfo.getPointerInfo();

        int x = pointerInfo.getLocation().x;
        int y = pointerInfo.getLocation().y;

        if(x == previousX && y == previousY) {
            inactivityInMin += 0.03f;
            System.out.println("Inactivity is now: " + inactivityInMin);

            if (goneAway) {
                if(inactivityStart.equals("m")) {
                    inactivityStart = getTime("HH:mm");
                    System.out.println("Putting time to inactivityStart string: " + inactivityStart);
                }

                System.out.println("Inactivity continues. Putting new end time & duration: " + inactivityInMin);
                inactivityStartEnd.put(inactivityStart, getTime("HH:mm"));
                inactivity.put(inactivityStart, inactivityInMin);

                HelloApplication.ss.SendInactivity();
            }
        }
        else {
            previousX = x;
            previousY = y;
            inactivityInMin = 0;
            goneAway = false;

            if(!statusAlreadySwitched) {
                HelloApplication.switchStatusTo("online");
                statusAlreadySwitched = true;
            }

        }

        if(inactivityInMin > HelloApplication.inactivityPeriod && !goneAway) {
            HelloApplication.switchStatusTo("offline");
            statusAlreadySwitched = false;
            goneAway = true;
            inactivityStart = "m";
        }
        else {
            ActivityTimeInMin += 0.03f;
        }

        //System.out.println("Global Activity Time in seconds: " + ActivityTimeInMin);
    }

    String getTime(String s) {
        LocalDateTime now = LocalDateTime.now();
        DateTimeFormatter formatter = DateTimeFormatter.ofPattern(s);
        return now.format(formatter);
    }

    private String getActiveWindowTitle() {
        char[] buffer = new char[1024];
        WinDef.HWND hwnd = User32.INSTANCE.GetForegroundWindow();
        User32.INSTANCE.GetWindowText(hwnd, buffer, buffer.length);
        return Native.toString(buffer);
    }
}