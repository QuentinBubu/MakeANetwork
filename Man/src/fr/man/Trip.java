package fr.man;

import java.util.Date;

public class Trip {
    private final Date depart;
    private final Stop destination;
    private final Stop origin;

    public Trip(Date depart, Stop destination, Stop origin) {
        this.depart = depart;
        this.destination = destination;
        this.origin = origin;
    }

    public Date getDepart() {
        return depart;
    }

    public Stop getDestination() {
        return destination;
    }

    public Stop getOrigin() {
        return origin;
    }
}
