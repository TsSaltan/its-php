<div class="row">
    <div class="col-lg-12">
        <h3>Текущий счёт</h3>
        <p>Ваш баланс: <b><?=$this->balance?></b> <?=$this->balanceCurrency?></p>
    </div>

    <div class="col-lg-12">
        <h3>Пополнение</h3>
        <?=$this->balancePayForm?>
    </div>

    <div class="col-lg-12">
        <h3>История операций</h3>
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Изменение счёта</th>
                        <th>Описание</th>
                        <th>Дата</th>
                    </tr>
                </thead>
                <tbody>
                    <?foreach ($this->vars['balanceHistory'] as $i => $item):?>
                    <tr>
                        <td><?=($i+1)?></td>
                        <td style='color:<?=($item['balance'] < 0) ? 'red' : 'green'?>'><?=$item['balance']?></td>
                        <td><?=$item['description']?></td>
                        <td><?=$item['timestamp']?></td>
                    </tr>
                    <?endforeach?>
                </tbody>
            </table>
        </div>
        <!-- /.table-responsive -->
    </div>
</div>