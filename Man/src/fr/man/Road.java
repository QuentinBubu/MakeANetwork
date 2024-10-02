package fr.man;

import java.util.ArrayList;
import java.util.Collections;

public class Road {
    private final Stop first;
    private final Stop last;
    private final double distance;

    public Road(Stop first, Stop last, double distance) {
        this.first = first;
        this.last = last;
        this.distance = distance;
    }

    public Stop getFirst() {
        return first;
    }

    public Stop getLast() {
        return last;
    }

    public double getDistance() {
        return distance;
    }

    @Override
    public int hashCode() {
        ArrayList<Stop> stops = new ArrayList<Stop>()
        {{
            add(first);
            add(last);
        }};
        Collections.sort(stops);
        return stops.hashCode();
    }
}
