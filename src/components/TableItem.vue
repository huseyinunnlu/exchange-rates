<template>
	<tr>
		<th scope="row">{{data.Code}}</th>
		<td>{{data.Unit}}</td>
		<td>{{data.CurrencyName}}</td>
		<td>{{data.ForexBuying}}</td>
		<td>{{data.ForexSelling}}</td>
		<td>{{data.BanknoteBuying}}</td>
		<td>{{data.BanknoteSelling}}</td>
	</tr>
</template>
<script>
	export default{
		props:['data'],
	}
</script>