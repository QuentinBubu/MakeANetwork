package fr.man;

import java.util.HashSet;
import java.util.Set;

public class Stop implements Comparable<Stop> {
    private final String name;
    private Set<Road> roads;
    private Set<Person> queue;

    public Stop(String name) {
        this.name = name;
        this.roads = new HashSet<>();
        this.queue = new HashSet<>();
    }

    public void addRoad(Road road) {
        this.roads.add(road);
    }

    public String getName() {
        return name;
    }

    public void add(Person person) {
        this.queue.add(person);
    }

    public Set<Road> getRoads() {
        return roads;
    }

    public Set<Person> getQueue() {
        return queue;
    }

    @Override
    public int compareTo(Stop o) {
        return this.name.compareTo(o.name);
    }
}
