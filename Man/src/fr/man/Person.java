package fr.man;

public class Person {
    private Trip round;
    private Trip back;
    private final String name;

    public Person(Trip back, Trip round, String name) {
        this.back = back;
        this.round = round;
        this.name = name;
    }

    public Trip getRound() {
        return round;
    }

    public Trip getBack() {
        return back;
    }

    public String getName() {
        return name;
    }
}
