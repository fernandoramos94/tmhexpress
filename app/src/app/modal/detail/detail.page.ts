import { Component, Input, OnInit } from '@angular/core';
import { ModalController } from '@ionic/angular';
import { BehaviorSubject } from 'rxjs';

@Component({
  selector: 'app-detail',
  templateUrl: './detail.page.html',
  styleUrls: ['./detail.page.scss'],
})
export class DetailPage implements OnInit {
  orderDetail:any = {}

  @Input() mySubject: BehaviorSubject<any>;

  constructor(private modalCtrl: ModalController) { }

  ngOnInit() {
    this.orderDetail = this.mySubject.value;
    console.log(this.orderDetail);
  }

}
