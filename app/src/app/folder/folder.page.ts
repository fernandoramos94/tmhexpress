import { Component, OnInit } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { ModalController } from '@ionic/angular';
import { ApiService } from '../services/api.service';
import {DetailPage} from '../modal/detail/detail.page';
import { BehaviorSubject } from 'rxjs';

@Component({
  selector: 'app-folder',
  templateUrl: './folder.page.html',
  styleUrls: ['./folder.page.scss'],
})
export class FolderPage implements OnInit {
  public folder: string;

  constructor(private activatedRoute: ActivatedRoute, private api : ApiService, private modalCtrl: ModalController) { }

  ngOnInit() {
    this.folder = this.activatedRoute.snapshot.paramMap.get('id');
    this.getOrder();
  }

  orders: any = [];
  detail : any ;

  getOrder(){
    let code = localStorage.getItem("code");

    this.api.getOrder(code).subscribe((resp:any) => {
      this.orders = resp.data;
      console.log(resp);
    })
  }

  async presentModal(item) {

    this.detail = item;
    const mySubject = new BehaviorSubject(this.detail);
    const modal = await this.modalCtrl.create({
      component: DetailPage,
      breakpoints: [0, 0.3, 0.5, 0.8],
      initialBreakpoint: 0.5,
      componentProps: {
        mySubject
      }
    });
    await modal.present();
  }

}
