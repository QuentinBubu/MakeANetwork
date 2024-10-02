package fr.man;

public class Bus {
    private final int max_capacity;
    private final double loadingSpeed;
    private final double movementSpeed;
    private final Road[] roads;


    public Bus(int max_capacity, double loadingSpeed, double movementSpeed, Road[] roads) {
        this.max_capacity = max_capacity;
        this.loadingSpeed = loadingSpeed;
        this.movementSpeed = movementSpeed;
        this.roads = roads;
    }

    public int getMax_capacity() {
        return max_capacity;
    }

    public double getLoadingSpeed() {
        return loadingSpeed;
    }

    public double getMovementSpeed() {
        return movementSpeed;
    }

    public Road[] getRoads() {
        return roads;
    }
}
