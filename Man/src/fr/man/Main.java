package fr.man;

import java.util.HashMap;
import java.util.HashSet;
import java.util.Set;

import static javax.swing.UIManager.put;

public class Main {
    public static void main(String[] args) {

        HashMap<String, Stop> stops = new HashMap<String, Stop>() {
            {
                {
                    put("A", new Stop("A"));
                    put("B", new Stop("B"));
                    put("C", new Stop("C"));
                    put("D", new Stop("D"));
                    put("E", new Stop("E"));
                }
            }
        };

        Set<Road> roads = new HashSet<Road>() {{
            put("AB", new Road(stops.get("A"), stops.get("B"), 10.0));
            put("AC", new Road(stops.get("A"), stops.get("C"), 4.0));
            put("CD", new Road(stops.get("C"), stops.get("D"), 12.0));
            put("CE", new Road(stops.get("C"), stops.get("E"), 4.0));
        }};

        Bus[] buses = new Bus[4];

        buses[0] = new Bus(10, 1, 30, );
        buses[1] = new Bus(10, 1, 30, );
        buses[2] = new Bus(10, 1, 30, );
        buses[3] = new Bus(2, 1, 10, );

    }
}
