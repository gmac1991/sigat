import conexao from "../config/dbConfig.js";


export async function listarChamadoOtobo(idTicket, dataVerificacaoChamado) {
    return conexao.conn_otobo
    .select('*')
    .table('article_data_mime')
    .innerJoin('article', 'article_data_mime.article_id', 'article.id')
    .where('article.ticket_id', idTicket)
    
    .andWhere('article.create_by', '!=', process.env.USER_SIGAT_OTOBO_ID)
    .modify(function(queryBuilder) {
        if (dataVerificacaoChamado != null) {
            queryBuilder.andWhere('article_data_mime.create_time', '>=', dataVerificacaoChamado);
        }
    });
}