import http from "./http-common";

class DataService{
    show_weather(data){
        return http.get("/weather")
    }

}

export default new DataService();

