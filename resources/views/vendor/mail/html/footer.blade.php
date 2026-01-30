<tr>
<td>
<table class="footer" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td class="content-cell" align="center">
<p style="font-size: 13px; color: #9ca3af; margin: 0;">
&copy; {{ date('Y') }} MixIncome. All rights reserved.
</p>
@if(trim($slot) !== '')
<div style="margin-top: 8px;">
{{ Illuminate\Mail\Markdown::parse($slot) }}
</div>
@endif
</td>
</tr>
</table>
</td>
</tr>
