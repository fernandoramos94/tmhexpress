import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { environment } from 'src/environments/environment';

@Injectable({
  providedIn: 'root'
})
export class ApiService {

  url = environment.url;

  constructor(private http: HttpClient) { }

  getOrder(code:any){
    return this.http.get(`${this.url}stopsDriver/${code}`);
  }
  getStatus(){
    return this.http.get<any>(`${this.url}status`);
  }
}
