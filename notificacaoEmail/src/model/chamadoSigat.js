import conexao from "../config/dbConfig.js";


export async function listarChamadoSigat() {
    return conexao.conn
    .select('*')
    .table('chamado')
    .where('status_chamado', 'ABERTO')
}

export async function alteraNotificacaoEmail(idChamado, countEmail, dataVerificacao) {
    return conexao.conn
    .table('chamado')
    .where({ id_chamado: idChamado })
    .update({ 
        data_verificacao_email_chamado: dataVerificacao,
        email_nao_lido_chamado: countEmail
    });
}