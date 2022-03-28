import { Component } from '@angular/core';

import { md5 } from "blueimp-md5";

@Component({
  selector: 'app-root',
  templateUrl: 'app.component.html',
  styleUrls: ['app.component.scss'],
})
export class AppComponent {
  public appPages = [
    { title: 'Pedidos', url: '/folder/Inbox', icon: 'mail' }
  ];
  code: any;
  constructor() {
    this.code = localStorage.getItem("code");

    if(!this.code){
      let co = Math.round(Math.random() * 100);
      let num = Math.floor(Math.random()*1E16);
      localStorage.setItem("code", ""+num);
      this.code = num;
      
    }


  }
}
