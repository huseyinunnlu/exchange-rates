<template>
	<div class="col-md-12">
		<div class="card">
			<div class="card-header d-flex justify-content-between align-items-center">
				<div class="d-flex align-items-center">
					<h4 class="me-2">Exchange List</h4>
					<small v-if="exchanges.results" class="text-muted" v-text="exchanges.results.length+' Exchanges'"></small>
				</div>
				<div class="form-group d-flex">
					<input type="date" class="form-control form-control-sm" v-model="date">
					<button class="btn btn-primary btn-sm" @click="getExchanges(date)" :disabled="!date">Search</button>
				</div>
			</div>
			<div class="card-body">
				<div class="text-center ">
					<div v-if="isLoading" class="spinner-border my-5" role="status" style="width: 6rem; height: 6rem; font-size: 30px;">
						<span class="sr-only"></span>
					</div>
				</div>

				<table v-if="!isLoading && exchanges.results.length > 0" class="table table-hover table-striped">
					<thead>
						<tr>
							<th scope="col">
								Döviz Kodu<br>
								<small class="text-muted">CurrencyCode</small>
							</th>
							<th scope="col">
								Birim<br>
								<small class="text-muted">Birim</small>
							</th>
							<th scope="col">
								Döviz Cinsi<br>
								<small class="text-muted">Currency</small>
							</th>
							<th scope="col">
								Döviz Alış<br>
								<small class="text-muted">Forex Buying</small>
							</th>
							<th scope="col">
								Döviz Satış<br>
								<small class="text-muted">Forex Selling</small>
							</th>
							<th scope="col">
								Efektif Alış<br>
								<small class="text-muted">Banknote Buying</small>
							</th>
							<th scope="col">
								Efektif Satış<br>
								<small class="text-muted">Banknote Selling</small>
							</th>
						</tr>
					</thead>
					<tbody>
						<TableItem v-for="exchange in exchanges.results" :key="exchange.row" :data="exchange"/>
					</tbody>
				</table>
			</div>
		</div>
		<div class="exchange">
			<div class="card">
				<div class="card-body row">
					<div class="list col-md-6 border">
						<div class="list-header border-bottom py-2 border-bottom my-2">
							<h3 class="text-center">Exchange History</h3>
						</div>
						<div class="list-body">
							<ul class="d-flex flex-column m-2 text-center" style="list-style: none;">
								<li class="py-2" v-for="(item,index) in exchangesList" :key="index">{{item}}</li>
							</ul>
						</div>
					</div>
					<div class="sel-buy col-md-6 border">
						<div class="sell-buy-header border-bottom py-2 border-bottom my-2">
							<h3 class="text-center">Exchange History</h3>
						</div>
						<div class="sell-buy-body">
							<form @submit.prevent="addExchange()">
								<div class="form-group row">
									<label class="col-sm-2">Ad Soyad</label>
									<div class="col-sm-8">
										<input type="text" class="form-control form-control-sm" placeholder="Ad Soyad" v-model="form.nameSurname">
									</div>
								</div>
								<div class="form-group row">
									<label class="col-sm-2">İşlem Türü</label>
									<div class="col-sm-8">
										<select class="form-select form-select-sm" v-model="form.type">
											<option :value="null">Tür Seç</option>
											<option :value="'buying'">Döviz Alım</option>
											<option :value="'selling'">Döviz Satım</option>
										</select>
									</div>
								</div>
								<div class="row">
									<div class="form-group row col-sm-7">
										<label class="col-sm-4">İşlem Miktarı</label>
										<div class="col-sm-8">
											<input type="number" class="form-control form-control-sm" placeholder="Miktar" v-model="form.amount">
										</div>
									</div>
								
									<div class="form-group row col-sm-5">
										<label class="col-sm-12" v-if="date">Tarih: {{date}}</label>
										<label class="col-sm-12" v-else>Tarih: Today</label>
									</div>
								</div>
								<div class="form-group row">
									<label class="col-sm-2" v-if="form.type == 'buying'|| !form.type">Alınacak Para Cinsi</label>
									<label class="col-sm-2" v-if="form.type == 'selling'">Satılacak Para Cinsi</label>
									<div class="col-sm-8">
										<select v-if="form.type == 'buying' || !form.type" class="form-select form-select-sm" v-model="form.currency">
											<option :value="[]">Seç</option>
											<option v-for="result in exchanges.results" :key="result.row" :value="[result.ForexBuying,result.CurrencyName]">{{result.CurrencyName}} ({{result.Code}}) - {{result.ForexBuying}}</option>
										</select>
										<select v-if="form.type == 'selling'" class="form-select form-select-sm" v-model="form.currency">
											<option :value="[]">Seç</option>
											<option v-for="result in exchanges.results" :key="result.row" :value="[result.ForexSelling,result.CurrencyName]">{{result.CurrencyName}} ({{result.Code}}) - {{result.ForexSelling}}</option>
										</select>
									</div>
								</div>
								<div class="form-group row">
									<label class="col-sm-2"></label>
									<div class="col-sm-8">
										<button class="btn btn-primary btn-sm" :disabled="!form.nameSurname||!form.type||!form.amount||form.currency == []">İşlem Yap</button>
									</div>
								</div>		
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>
<script>
	import axios from 'axios'
	import TableItem from '@/components/TableItem.vue'
	export default {
		components:{
			TableItem,
		},
		data(){
			return {
				exchanges:[],
				date:null,
				isLoading:false,
				form:{
					nameSurname:null,
					type:null,
					amount:null,
					currency:[],
				},
				exchangesList:[],
			}
		},
		created(){
			this.getExchanges('today')
		},
		methods:{
			getExchanges(date){
				this.isLoading = true
				let sentDate = null;
				if (date != 'today') {
					let YYYY = date.slice(0,4)
					let MM = date.slice(5,7)
					let DD = date.slice(8,10)
					sentDate = DD+MM+YYYY
				}else{
					sentDate = date
				}
				axios.get('http://localhost:8000/api/'+sentDate)
				.then(res=>{
					this.exchanges = res.data
				})
				.catch(()=>{
					this.exchanges = []
				})
				.finally(()=>{
					this.isLoading = false
				})
			},
			addExchange(){
				let type = null;
				let date = null;
				const d = new Date();
				if (this.form.type == 'buying') {
					type = 'aldı'
				}else{
					type = 'sattı'
				}
				if (!this.date) {
					date = d.getFullYear()+'-'+d.getMonth()+'-'+d.getDate()
				}else{
					date = this.date
				}
				let tlamount = this.form.amount * this.form.currency[0]
				this.exchangesList.unshift(this.form.nameSurname+' '+ date + ' tarihinde ' + this.form.amount + ' ' + this.form.currency[1] + ' ' + type + ' ' + tlamount+'Tl.')
			},
		}
	}
</script>
<style>
	.sell-buy-body .form-group {
		margin: 15px 0;
	}
</style>