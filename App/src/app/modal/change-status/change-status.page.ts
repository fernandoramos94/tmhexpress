import { Component, OnInit } from '@angular/core';
import { ApiService } from 'src/app/services/api.service';

@Component({
  selector: 'app-change-status',
  templateUrl: './change-status.page.html',
  styleUrls: ['./change-status.page.scss'],
})
export class ChangeStatusPage implements OnInit {

  status:any = [];

  constructor(private api: ApiService) { }

  ngOnInit() {
    this.api.getStatus().subscribe((resp) => {
      this.status = resp;
    })
  }

}
