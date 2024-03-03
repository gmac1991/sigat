import {
    listarChamadoSigat,
    alteraNotificacaoEmail
} from "../model/chamadoSigat.js";
import {listarChamadoOtobo} from "../model/chamadoOtobo.js";

class ChamadoSigat {
    static async atualizarNotificacao() {
        var chamados_sigat = await listarChamadoSigat().then((data) => {
            return data;
        }).catch(err => {
            console.error(err);
            return err;
        });

        let dataVerificacao = new Date();

        chamados_sigat.forEach(function(chamado, i) {
            let dataChamado = null;

            if (chamado.data_verificacao_email_chamado == null) {
                dataChamado = new Date(chamado.data_chamado);
            } else {
                dataChamado = new Date(chamado.data_verificacao_email_chamado);
            }
            dataChamado.setHours(dataChamado.getHours() + 3);

            listarChamadoOtobo(chamado.id_ticket_chamado, dataChamado).then((data) => {
                chamado.email_otobo = data;
            }).catch(err => {
                console.error(err);
            }).finally(() => {
                let email_nao_lidos = chamado.email_otobo.length + chamado.email_nao_lido_chamado;

                alteraNotificacaoEmail(chamado.id_chamado, email_nao_lidos, dataVerificacao).catch((err) => {
                    console.log(err);
                });
            });
        });
    }
}

export default ChamadoSigat